import FreeSimpleGUI as sg
import math

# Define helper functions
def is_point_in_shape(point, shape):
    x, y = point
    sx, sy = shape['x'], shape['y']
    size = shape['size']
    half = size // 2
    if shape['type'] == 'square':
        return (sx - half <= x <= sx + half) and (sy - half <= y <= sy + half)
    elif shape['type'] == 'circle':
        dx, dy = x - sx, y - sy
        return dx**2 + dy**2 <= (half)**2
    # Add other shapes with bounding box check
    return False

def get_edge_point(point, shape):
    x, y = point
    sx, sy = shape['x'], shape['y']
    size = shape['size']
    half = size // 2
    if shape['type'] == 'square':
        left, right = sx - half, sx + half
        top, bottom = sy - half, sy + half
        threshold = 5
        if abs(x - left) < threshold and (top <= y <= bottom):
            return (left, y)
        elif abs(x - right) < threshold and (top <= y <= bottom):
            return (right, y)
        elif abs(y - top) < threshold and (left <= x <= right):
            return (x, top)
        elif abs(y - bottom) < threshold and (left <= x <= right):
            return (x, bottom)
    elif shape['type'] == 'circle':
        dx, dy = x - sx, y - sy
        dist = math.sqrt(dx**2 + dy**2)
        if abs(dist - half) < 5:
            angle = math.atan2(dy, dx)
            ex = sx + half * math.cos(angle)
            ey = sy + half * math.sin(angle)
            return (ex, ey)
    return None

# Define layout
shapes = []
connections = []
current_drag = None
temp_line = None

layout = [
    [sg.Combo(['square', 'circle', 'triangle', 'pentagon', 'hexagon'], key='-SHAPE-',default_value='circle', size=(10,1)),
    sg.Combo(['red', 'blue', 'green', 'yellow', 'magenta', 'cyan', 'purple'], key='-COLOR-',default_value='red', size=(10,1))],
    [sg.Graph(
        canvas_size=(800, 600),
        graph_bottom_left=(0, 600),
        graph_top_right=(800, 0),
        key='-GRAPH-',
        enable_events=True,
        drag_submits=True,
        background_color='black'
    )]
]

window = sg.Window('Shape Drawer', layout, finalize=True)
graph = window['-GRAPH-']

while True:
    event, values = window.read()
    if event == sg.WIN_CLOSED:
        break

    if event == '-GRAPH-':
        x, y = values['-GRAPH-']
        # Right-click handling
        if 'Right' in event or '+3' in event:
            target_shape = next((s for s in shapes if is_point_in_shape((x,y), s)), None)
            menu = ['Erase'] if target_shape else ['Add']
            chosen = sg.popup_menu(menu, location=(x,y), keep_on_top=True)
            if chosen == 'Add':
                shape_type = values['-SHAPE-']
                color = values['-COLOR-']
                size = 50
                if shape_type == 'square':
                    half = size//2
                    id = graph.draw_rectangle((x-half, y-half), (x+half, y+half), fill_color=color)
                elif shape_type == 'circle':
                    id = graph.draw_circle((x,y), size//2, fill_color=color)
                # Add other shapes similarly
                text_id = graph.draw_text(shape_type, (x,y), color='white')
                shapes.append({'id': id, 'text_id': text_id, 'type': shape_type, 'color': color, 'x':x, 'y':y, 'size': size})
            elif chosen == 'Erase' and target_shape:
                graph.delete_figure(target_shape['id'])
                graph.delete_figure(target_shape['text_id'])
                shapes.remove(target_shape)

        # Left-click drag handling
        elif 'Left' in event:
            if '+DOWN' in event:
                for shape in shapes:
                    edge = get_edge_point((x,y), shape)
                    if edge:
                        current_drag = {'shape': shape, 'start': edge}
                        temp_line = graph.draw_line(edge, (x,y))
                        break
            elif '+MOVE' in event and current_drag:
                graph.delete_figure(temp_line)
                temp_line = graph.draw_line(current_drag['start'], (x,y))
            elif '+UP' in event and current_drag:
                graph.delete_figure(temp_line)
                end_shape = next((s for s in shapes if s != current_drag['shape'] and is_point_in_shape((x,y), s)), None)
                if end_shape:
                    end_edge = get_edge_point((x,y), end_shape)
                    if end_edge:
                        graph.draw_line(current_drag['start'], end_edge, arrow='last')
                current_drag = None

window.close()