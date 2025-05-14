import tkinter as tk
from tkinter import ttk

class ShapeDrawer:
    def __init__(self, root):
        self.root = root
        self.root.title("Shape Drawer with Tkinter")

        # Canvas setup
        self.canvas = tk.Canvas(root, width=800, height=600, bg="lightblue")
        self.canvas.pack(fill=tk.BOTH, expand=True)

        # Shape and color selection
        self.shape_var = tk.StringVar(value="Square")
        self.color_var = tk.StringVar(value="red")

        shape_options = ["Square", "Circle", "Triangle", "Pentagon", "Hexagon"]
        color_options = ["red", "blue", "green", "yellow", "magenta", "cyan", "purple"]

        shape_menu = ttk.Combobox(root, textvariable=self.shape_var, values=shape_options, state="readonly")
        shape_menu.pack(side=tk.LEFT, padx=10, pady=10)

        color_menu = ttk.Combobox(root, textvariable=self.color_var, values=color_options, state="readonly")
        color_menu.pack(side=tk.LEFT, padx=10, pady=10)

        # Right-click menu
        self.menu = tk.Menu(root, tearoff=0)
        self.menu.add_command(label="Connect", command=self.start_connection)
        self.menu.add_command(label="Erase", command=self.erase_shape)

        # Bind mouse events
        self.canvas.bind("<Button-1>", self.draw_shape)
        self.canvas.bind("<Button-3>", self.show_menu)

        # Variables for connection
        self.connection_start = None
        self.shapes = []  # Store all shapes and their properties

    def draw_shape(self, event):
        """Draw a shape at the clicked location."""
        shape_type = self.shape_var.get()
        color = self.color_var.get()
        x, y = event.x, event.y
        size = 50  # Default size for shapes

        if shape_type == "Square":
            shape_id = self.canvas.create_rectangle(
                x - size, y - size, x + size, y + size, fill=color, outline="black"
            )
        elif shape_type == "Circle":
            shape_id = self.canvas.create_oval(
                x - size, y - size, x + size, y + size, fill=color, outline="black"
            )
        elif shape_type == "Triangle":
            points = [x, y - size, x - size, y + size, x + size, y + size]
            shape_id = self.canvas.create_polygon(points, fill=color, outline="black")
        elif shape_type == "Pentagon":
            points = self.calculate_polygon_points(x, y, size, sides=5)
            shape_id = self.canvas.create_polygon(points, fill=color, outline="black")
        elif shape_type == "Hexagon":
            points = self.calculate_polygon_points(x, y, size, sides=6)
            shape_id = self.canvas.create_polygon(points, fill=color, outline="black")

        # Add text (shape name) in the middle of the shape
        text_id = self.canvas.create_text(x, y, text=shape_type, fill="white")

        # Store shape and text IDs along with their properties
        self.shapes.append({"shape_id": shape_id, "text_id": text_id, "x": x, "y": y})

    def calculate_polygon_points(self, x, y, size, sides):
        """Calculate points for a regular polygon."""
        import math
        points = []
        for i in range(sides):
            angle = 2 * math.pi * i / sides
            px = x + size * math.cos(angle)
            py = y + size * math.sin(angle)
            points.extend([px, py])
        return points

    def show_menu(self, event):
        """Show the right-click menu."""
        self.menu.post(event.x_root, event.y_root)

    def start_connection(self):
        """Start connecting shapes."""
        self.connection_start = None
        self.canvas.bind("<Button-1>", self.select_shape_for_connection)

    def select_shape_for_connection(self, event):
        """Select shapes to connect."""
        x, y = event.x, event.y
        shape = self.find_shape_at(x, y)
        if shape:
            if self.connection_start is None:
                self.connection_start = shape
            else:
                self.draw_connection(self.connection_start, shape)
                self.connection_start = None
                self.canvas.unbind("<Button-1>")
                self.canvas.bind("<Button-1>", self.draw_shape)

    def draw_connection(self, shape1, shape2):
        """Draw an arrow (connection) between two shapes."""
        x1, y1 = shape1["x"], shape1["y"]
        x2, y2 = shape2["x"], shape2["y"]

        # Draw a black line with a yellow arrowhead
        self.canvas.create_line(x1, y1, x2, y2, arrow=tk.LAST, arrowshape=(8, 10, 5), fill="black")
        self.canvas.create_polygon(
            x2, y2, x2 - 10, y2 - 5, x2 - 10, y2 + 5, fill="yellow", outline="black"
        )

    def erase_shape(self):
        """Erase the shape under the cursor."""
        self.canvas.bind("<Button-1>", self.delete_shape_at_click)

    def delete_shape_at_click(self, event):
        """Delete the shape at the clicked location."""
        x, y = event.x, event.y
        shape = self.find_shape_at(x, y)
        if shape:
            self.canvas.delete(shape["shape_id"])
            self.canvas.delete(shape["text_id"])
            self.shapes.remove(shape)

    def find_shape_at(self, x, y):
        """Find the shape at the given coordinates."""
        for shape in self.shapes:
            shape_id = shape["shape_id"]
            coords = self.canvas.coords(shape_id)
            if coords:
                if shape["shape_type"] == "Square" or shape["shape_type"] == "Circle":
                    x1, y1, x2, y2 = coords
                    if x1 <= x <= x2 and y1 <= y <= y2:
                        return shape
                elif shape["shape_type"] in ["Triangle", "Pentagon", "Hexagon"]:
                    # Approximate check for polygons
                    x_center, y_center = shape["x"], shape["y"]
                    if abs(x - x_center) <= 50 and abs(y - y_center) <= 50:
                        return shape
        return None

if __name__ == "__main__":
    root = tk.Tk()
    app = ShapeDrawer(root)
    root.mainloop()