import FreeSimpleGUI as sg
import sqlite3
import csv
import os

import sqlite3
import csv
import os

sg.theme('DarkBlue')

class DatabaseManager:
    def __init__(self, db_file='database.db'):
        self.db_file = db_file
        
    def connect(self):
        return sqlite3.connect(self.db_file)
    
    def get_tables(self):
        with self.connect() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
            return [row[0] for row in cursor.fetchall() if row[0] != 'sqlite_sequence']
    
    def get_table_data(self, table_name):
        with self.connect() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute(f"PRAGMA table_info({table_name})")
            columns = [column[1] for column in cursor.fetchall()]
            cursor.execute(f"SELECT * FROM {table_name}")
            return columns, [dict(row) for row in cursor.fetchall()]
    
    def import_csv(self, filename, table_name):
        with open(filename, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(f)
            header = next(reader)
            rows = [row for row in reader]
        
        with self.connect() as conn:
            cursor = conn.cursor()
            cursor.execute(f"DROP TABLE IF EXISTS {table_name}")
            cursor.execute(f"""
                CREATE TABLE {table_name} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    {', '.join([f'"{col}" TEXT' for col in header[0:]])}
                )
            """)
            cursor.executemany(f"""
                INSERT INTO {table_name} ({', '.join(header)})
                VALUES ({', '.join(['?']*len(header))})
            """, rows)
            conn.commit()
    
    def update_table(self, table_name, data):
        with self.connect() as conn:
            cursor = conn.cursor()
            cursor.execute(f"PRAGMA table_info({table_name})")
            columns = [column[1] for column in cursor.fetchall() if column[1] != 'id']
            
            try:
                # Update existing and insert new rows
                for row in data:
                    if 'id' in row and row['id'] is not None:
                        # Update existing row
                        set_clause = ', '.join([f'"{col}"=?' for col in columns])
                        values = [row[col] for col in columns] + [row['id']]
                        cursor.execute(f"UPDATE {table_name} SET {set_clause} WHERE id=?", values)
                    else:
                        # Insert new row
                        cols = ', '.join([f'"{col}"' for col in columns])
                        placeholders = ', '.join(['?'] * len(columns))
                        values = [row.get(col, '') for col in columns]
                        cursor.execute(f"INSERT INTO {table_name} ({cols}) VALUES ({placeholders})", values)
                conn.commit()
                return True
            except Exception as e:
                sg.popup_error(f"Save failed: {str(e)}")
                return False

def show_table_window(table_name):
    db = DatabaseManager()
    columns, data = db.get_table_data(table_name)
    display_columns = columns[1:]  # Skip ID column
    display_data = []
    modified_rows = set()
    new_rows = set()

    # Initialize display data
    for idx, row in enumerate(data):
        if 'id' not in row:
            new_rows.add(idx)
        display_data.append([row[col] for col in display_columns])

    layout = [
        [sg.Text(f"Table: {table_name}", font='Any 12')],
        [sg.Table(values=display_data, headings=display_columns, key='-TABLE-',
                 auto_size_columns=True,
                 justification='left',
                 num_rows=min(20, len(display_data)),
                 enable_events=True,
                 enable_click_events=True,
                 row_colors=[(i, 'black', '#FFFF00') for i in modified_rows.union(new_rows)])],
        [sg.Button('Add Row'), sg.Button('Save'), sg.Button('Close')]
    ]

    window = sg.Window(f"Table Editor - {table_name}", layout, finalize=True)

    while True:
        event, values = window.read()

        if event in (sg.WIN_CLOSED, 'Close'):
            break

        if event == 'Add Row':
            # Add new row with empty values
            new_row = {col: '' for col in display_columns}
            data.append(new_row)
            display_data.append(['' for _ in display_columns])
            new_rows.add(len(data)-1)
            window['-TABLE-'].update(values=display_data, 
                                   row_colors=[(i, 'black', '#FFFF00') for i in modified_rows.union(new_rows)])

        elif event == 'Save':
            if db.update_table(table_name, data):
                # Refresh data after save
                columns, new_data = db.get_table_data(table_name)
                data.clear()
                data.extend(new_data)
                display_data.clear()
                for row in data:
                    display_data.append([row[col] for col in display_columns])
                modified_rows.clear()
                new_rows.clear()
                window['-TABLE-'].update(values=display_data, row_colors=[])
                sg.popup("Changes saved successfully!")

        elif isinstance(event, tuple) and event[0] == '-TABLE-':
            row_idx, col_idx = event[2][0], event[2][1]
            if row_idx >= 0 and col_idx >= 0:
                # Edit cell
                current_value = data[row_idx].get(display_columns[col_idx], '')
                new_value = sg.popup_get_text('Edit Cell Value:', default_text=current_value)
                if new_value is not None and new_value != current_value:
                    data[row_idx][display_columns[col_idx]] = new_value
                    display_data[row_idx][col_idx] = new_value
                    modified_rows.add(row_idx)
                    window['-TABLE-'].update(values=display_data, 
                                            row_colors=[(i, 'black', '#FFFF00') for i in modified_rows.union(new_rows)])

    window.close()
sizearray=[0.5,0.75,1,1.5,2,3]
for i in range(4,64,2):
    sizearray.append(i)
tiparray=['flat','forward','reverse']
def design_window():
    sizes = [str(x/2) if x < 3 else str(x) for x in range(1, 69) if x in (1, 2, 3, 4, 6, 8) or x/2 in (0.5, 0.75, 1.0, 1.5)]
    types = ['reverse', 'forward', 'flat']
    
    layout = [
        [sg.Text('Design Parameters', font='Any 14')],
        [sg.Text('Size (inches):'), sg.Spin(sizearray, key='-SIZE-',initial_value=sizearray[2],s=(4,4)),sg.Text('Type:'), sg.Spin(tiparray, key='-TYPE-',initial_value=tiparray[1],s=(8,2))],
        [sg.Text('Burst Pressure (psi):')],
        [sg.Slider(range=(0.11, 180.63), resolution=0.01, expand_x=True, key='-PRESSURE-',orientation='h')],
        [sg.Text('Burst Temperature (°F):')],
        [sg.Slider(range=(-100, 800), resolution=1, expand_x=True, key='-TEMP-',orientation='h')],
        [sg.Button('Design'), sg.Button('Cancel')],
        [sg.Table(values=[], headings=['Size', 'Type', 'RBP', 'Material', 'Rating'], 
                 key='-RESULTS-', auto_size_columns=True, visible=False)]
    ]
    
    window = sg.Window('Design Calculator', layout)
    
    while True:
        event, values = window.read()
        if event in (sg.WIN_CLOSED, 'Cancel'):
            break
            
        if event == 'Design':
            # Validate inputs
            try:
                size = str(values['-SIZE-'])
                type_ = str(values['-TYPE-'])
                for i in range(3):
                    if tiparray[i] is values['-TYPE-']:
                        type_ = i
                type_ = str(values['-TYPE-'])
                pressure = float(values['-PRESSURE-'])
                temp = float(values['-TEMP-'])
            except:
                sg.popup_error('Invalid input values!')
                continue
                
            # Search database
            db = DatabaseManager()
            with db.connect() as conn:
                cursor = conn.cursor()
                cursor.execute(f"""
                    SELECT * FROM tsts 
                    WHERE size = {size}
                    AND type = {type_}
                    
                """)
                result = cursor.fetchone()
                
            if result:
                # Convert result to display format
                display_data = [
                    [
                        
                        result[4],
                        result[5],
                        result[1],
                        result[2],
                        result[3]
                    ]
                ]
                window['-RESULTS-'].update(values=display_data, visible=True)
            else:
                sg.popup('No matching design found!')
                
    window.close()

def main():
    db = DatabaseManager()
    
    if not os.path.exists('database.db'):
        open('database.db', 'w').close()
    menu_layout = [
        ['File', ['New', 'Exit']],
        ['Database', ['Add CSV', 'Refresh']]
    ]
    layout = [
         [sg.Menu(menu_layout)],
        [sg.Text("Available Tables:")],
        [sg.Listbox(values=[], size=(30, 10), key='-TABLE-LIST-', enable_events=True)],
        
       
        [sg.Listbox(values=[], size=(30, 10), key='-TABLE-LIST-', enable_events=True)],
        [sg.Button('Exit')]
    ]

    window = sg.Window('Database Manager', layout, finalize=True)

    def refresh_tables():
        tables = db.get_tables()
        window['-TABLE-LIST-'].update(tables)

    refresh_tables()

    while True:
        event, values = window.read()

        if event in (sg.WIN_CLOSED, 'Exit'):
            break

        if event == 'Add CSV':
            filename = sg.popup_get_file('Select CSV file', file_types=(("CSV Files", "*.csv"),))
            if filename:
                table_name = os.path.splitext(os.path.basename(filename))[0]
                try:
                    db.import_csv(filename, table_name)
                    refresh_tables()
                    sg.popup(f"Table '{table_name}' imported successfully!")
                except Exception as e:
                    sg.popup_error(f"Import failed: {str(e)}")

        elif event == 'Refresh':
            refresh_tables()

        elif event == '-TABLE-LIST-':
            if values['-TABLE-LIST-']:
                table_name = values['-TABLE-LIST-'][0]
                show_table_window(table_name)
        elif event == 'New':
            design_window()
            
    window.close()

if __name__ == '__main__':
    main()