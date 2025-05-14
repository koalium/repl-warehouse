import FreeSimpleGUI as sg
def ColumnFixedSize(layout, size=(None, None), *args, **kwargs):
    # An addition column is needed to wrap the column with the Sizers because the colors will not be set on the space the sizers take
    return sg.Column([[sg.Column([[sg.Sizer(0,size[1]-1), sg.Column([[sg.Sizer(size[0]-2,0)]] + layout, *args, **kwargs, pad=(0,0))]], *args, **kwargs)]],pad=(0,0))

selected_shape=[]
figlist=[]
def drawfigure(fig):
    global figlist
    figlist.append(fig)
def main():
    sg.theme('Dark Blue 1')
    global figures
    figures =[]
    # Define the combo box for selecting the drawing mode
    modes = ['Rectangle', 'Circle', 'Line', 'Point', 'Erase', 'select', 'Move', 'Clear']
    graph_right_click_menu=[[''], ['Erase item', 'Send to back', 'Connect']]

    
    global figure
    global selected_figures
    colinter_1 = [[sg.Graph(
            canvas_size=(1600, 800),
            graph_bottom_left=(0, 0),
            graph_top_right=(1595, 795),
            key="-GRAPH-",
            enable_events=True,
            background_color='olive',
            drag_submits=True,
            motion_events=True,
            right_click_menu=graph_right_click_menu
        )]]
        
    
    
    colinter2=[[sg.Combo(modes, default_value='Rectangle', key='-MODE-', enable_events=True, size=(10, 1)),sg.Stretch()],[sg.Text(key='-INFO-', size=(60, 1))],
               [sg.Listbox([],k='selected-shape-list',s=(20,8),horizontal_scroll=False,justification='c'),sg.Stretch()],
               [sg.CButton('cbutt,aut',auto_size_button=True,bind_return_key=True,k='btncb'),sg.Stretch()]
               ]

    col1=sg.Column(colinter_1, element_justification='c', size=(1600, 800), background_color='black')
    col2=sg.Column(colinter2, element_justification='c', size=(200, 800), justification='c',vertical_scroll_only=True)
    
    layout=[[col2,col1]]
    #layout=[ [ColumnFixedSize(laycol1, size=(500, 300), element_justification='c', vertical_alignment='t')]]
    window = sg.Window("Drawing and Moving Stuff Around", layout, finalize=True)

    # Get the graph element for ease of use later
    gr=graph = window["-GRAPH-"]  # type: sg.Graph
    dragging = False
    start_point = end_point = prior_rect = None
    crosshair_lines = []
    selected_figures = []  # To store selected figures for connection
    temp_line = None  # Temporary line for drawing connections
    
    while True:
        event, values = window.read()
        print(event, values)
        if event == sg.WIN_CLOSED:
            break  # exit

        # Handle mouse motion events
        if event.endswith('+MOVE'):
            window["-INFO-"].update(value=f"mouse {values['-GRAPH-']}")

        # Delete crosshairs if any exist
        if len(crosshair_lines):
            for fig in crosshair_lines:
                graph.delete_figure(fig)
            crosshair_lines = []
            window.refresh()

        # Handle graph events (mouse clicks and drags)
        if event == "-GRAPH-":  # If there's a "Graph" event, then it's a mouse event
            x, y = values["-GRAPH-"]
            
            if not dragging:
                start_point = (x, y)
                dragging = True
                drag_figures = graph.get_figures_at_location((x, y))
                
                
                lastxy = x, y
            else:
                end_point = (x, y)
            if prior_rect:
                graph.delete_figure(prior_rect)
            delta_x, delta_y = x - lastxy[0], y - lastxy[1]
            lastxy = x, y
            if None not in (start_point, end_point):
                mode = values['-MODE-']
                if mode == 'Move':
                    for fig in drag_figures:
                        graph.move_figure(fig, delta_x, delta_y)
                        graph.update()
                elif mode == 'Rectangle':
                    prior_rect = graph.draw_rectangle(start_point, end_point, fill_color='green', line_color='red')
                    
                elif mode == 'Circle':
                    prior_rect = graph.draw_circle(start_point, end_point[0] - start_point[0], fill_color='red', line_color='green')
                elif mode == 'Line':
                    prior_rect = graph.draw_line(start_point, end_point, width=4)
                elif mode == 'Point':
                    graph.draw_point((x, y), size=8)
                elif mode == 'Erase':
                    for figure in drag_figures:
                        graph.delete_figure(figure)
                elif mode == 'Clear':
                    graph.erase()
                
                elif mode == 'select':
                    figures = graph.get_figures_at_location(values['-GRAPH-'])
                    if figures:
                        selected_figures.append(figures[-1])  # Select the top-most figure
                        
                        
                        if len(selected_figures) == 2:  # If two figures are selected
                            # Get the center of the first figure
                            fig1_bounds = graph.get_bounding_box(selected_figures[0])
                            fig1_center = ((fig1_bounds[0][0] + fig1_bounds[1][0]) / 2, ((fig1_bounds[0][1] + fig1_bounds[1][1]) / 2))
                            # Get the center of the second figure
                            fig2_bounds = graph.get_bounding_box(selected_figures[1])
                            fig2_center = ((fig2_bounds[0][0] + fig2_bounds[1][0]) / 2, ((fig2_bounds[0][1] + fig2_bounds[1][1]) / 2))
                            # Draw a line with an arrow (yellow triangle at the end)
                            graph.draw_line(fig1_center, fig2_center, width=2, color='black')
                            selected_figures = []  # Reset selection
                

            window["-INFO-"].update(value=f"mouse {values['-GRAPH-']}")
        elif event.endswith('+UP'):  # The drawing has ended because mouse up
            window["-INFO-"].update(value=f"grabbed rectangle from {start_point} to {end_point}")
            start_point, end_point = None, None  # Enable grabbing a new rect
            dragging = False
            prior_rect = None
        elif event == 'Send to back':  # Right-clicked menu item
            figures = graph.get_figures_at_location(values["-GRAPH-"])  # Get items in front-to-back order
            if figures:  # Make sure at least 1 item found
                graph.send_figure_to_back(figures[-1])  # Get the last item which will be the top-most
        elif event == 'Erase item':
            window["-INFO-"].update(value=f"Right click erase at {values['-GRAPH-']}")
            if values['-GRAPH-'] != (None, None):
                figures = graph.get_figures_at_location(values['-GRAPH-'])
                if figures:
                    graph.delete_figure(figures[-1])  # Delete the one on top
        elif event == 'Connect':  # Right-click "Connect" option
            if values['-GRAPH-'] != (None, None):
                figures = graph.get_figures_at_location(values['-GRAPH-'])
                if figures:
                    selected_figures.append(figures[-1])  # Select the top-most figure
                    if len(selected_figures) == 2:  # If two figures are selected
                        # Get the center of the first figure
                        fig1_bounds = graph.get_bounding_box(selected_figures[0])
                        fig1_center = ((fig1_bounds[0][0] + fig1_bounds[1][0]) / 2, ((fig1_bounds[0][1] + fig1_bounds[1][1]) / 2))
                        # Get the center of the second figure
                        fig2_bounds = graph.get_bounding_box(selected_figures[1])
                        fig2_center = ((fig2_bounds[0][0] + fig2_bounds[1][0]) / 2, ((fig2_bounds[0][1] + fig2_bounds[1][1]) / 2))
                        # Draw a line with an arrow (yellow triangle at the end)
                        graph.draw_line(fig1_center, fig2_center, width=2, color='black')
                        selected_figures = []  # Reset selection
        location = values['-GRAPH-']
        
        figures = graph.get_figures_at_location(values['-GRAPH-'])   
        if figures is not None and event.endswith('+UP'):
            
            for d in figures:
                if d in selected_shape:
                    continue
                selected_shape.append(d)
            
            
            
            window['selected-shape-list'].update(selected_shape)
        crosshair_lines = [graph.draw_line((location[0], 0), (location[0], 800), color='red'),
                           graph.draw_line((0, location[1]), (1600, location[1]), color='red')]

    window.close()

main()