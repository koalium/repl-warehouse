import FreeSimpleGUI as sg
import sqlite3
import csv
import os
import math

sg.theme('DarkRed')

# Database Manager class remains the same as previous
# ... [Previous DatabaseManager class code here] ...

def design_window():
    sizes = [str(x/2) if x < 3 else str(x) for x in range(1, 69) if x in (1, 2, 3, 4, 6, 8) or x/2 in (0.5, 0.75, 1.0, 1.5)]
    types = ['reverse', 'forward', 'flat']
    
    layout = [
        [sg.Text('Design Parameters', font='Any 14')],
        [sg.Text('Size (inches):'), sg.Combo(sizes, key='-SIZE-')],
        [sg.Text('Type:'), sg.Combo(types, key='-TYPE-')],
        [sg.Text('Burst Pressure (psi):')],
        [sg.Slider(range=(0.11, 180.63), resolution=0.01, expand_x=True, key='-PRESSURE-')],
        [sg.Text('Burst Temperature (°F):')],
        [sg.Slider(range=(-100, 800), resolution=1, expand_x=True, key='-TEMP-')],
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
                size = float(values['-SIZE-'])
                type_ = values['-TYPE-']
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
                    SELECT *, ABS(rbp - {pressure}) AS diff 
                    FROM design_data 
                    WHERE size = {size} 
                      AND type = '{type_}'
                      AND temp_min <= {temp} 
                      AND temp_max >= {temp}
                    ORDER BY diff
                    LIMIT 1
                """)
                result = cursor.fetchone()
                
            if result:
                # Convert result to display format
                display_data = [
                    [
                        result['size'],
                        result['type'],
                        f"{result['rbp']:.2f}",
                        result['material'],
                        result['rating']
                    ]
                ]
                window['-RESULTS-'].update(values=display_data, visible=True)
            else:
                sg.popup('No matching design found!')
                
    window.close()

# Modified main menu layout
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
        [sg.Button('Exit')]
    ]

    window = sg.Window('Database Manager', layout)

    # ... [Rest of main function remains the same] ...
    
    while True:
        event, values = window.read()

        if event in (sg.WIN_CLOSED, 'Exit'):
            break
            
        # Add new event handler for File->New
        if event == 'New':
            design_window()
            
        # ... [Rest of event handling remains the same] ...

if __name__ == '__main__':
    main()