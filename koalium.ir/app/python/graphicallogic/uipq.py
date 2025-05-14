import sys
from PyQt5.QtWidgets import (
    QApplication,
    QMainWindow,
    QWidget,
    QVBoxLayout,
    QHBoxLayout,
    QComboBox,
    QLabel,
    QMenu,
)
from PyQt5.QtGui import QPainter, QColor, QPen, QBrush, QPolygonF
from PyQt5.QtCore import Qt, QPointF


class ShapeDrawer(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Shape Drawer with PyQt5")
        self.setGeometry(100, 100, 800, 600)

        # Central widget and layout
        self.central_widget = QWidget()
        self.setCentralWidget(self.central_widget)
        self.layout = QVBoxLayout(self.central_widget)

        # Shape and color selection
        self.shape_combo = QComboBox()
        self.shape_combo.addItems(["Square", "Circle", "Triangle", "Pentagon", "Hexagon"])
        self.color_combo = QComboBox()
        self.color_combo.addItems(["red", "blue", "green", "yellow", "magenta", "cyan", "purple"])

        # Add widgets to layout
        self.layout.addWidget(QLabel("Select Shape:"))
        self.layout.addWidget(self.shape_combo)
        self.layout.addWidget(QLabel("Select Color:"))
        self.layout.addWidget(self.color_combo)

        # Canvas (custom QWidget)
        self.canvas = Canvas(self)
        self.layout.addWidget(self.canvas)

        # Right-click menu
        self.canvas.setContextMenuPolicy(Qt.CustomContextMenu)
        self.canvas.customContextMenuRequested.connect(self.show_context_menu)

        # Variables for connection
        self.connection_start = None
        self.shapes = []  # Store all shapes and their properties

    def show_context_menu(self, pos):
        """Show the right-click menu."""
        menu = QMenu(self)
        connect_action = menu.addAction("Connect")
        erase_action = menu.addAction("Erase")
        action = menu.exec_(self.canvas.mapToGlobal(pos))

        if action == connect_action:
            self.canvas.start_connection()
        elif action == erase_action:
            self.canvas.erase_shape()


class Canvas(QWidget):
    def __init__(self, parent):
        super().__init__(parent)
        self.setMouseTracking(True)
        self.shapes = []
        self.connection_start = None
        self.erase_mode = False

    def paintEvent(self, event):
        """Draw all shapes and connections."""
        painter = QPainter(self)
        painter.setRenderHint(QPainter.Antialiasing)

        # Draw shapes
        for shape in self.shapes:
            painter.setBrush(QBrush(QColor(shape["color"])))
            painter.setPen(QPen(Qt.black, 2))
            if shape["type"] == "Square":
                painter.drawRect(shape["x"] - 25, shape["y"] - 25, 50, 50)
            elif shape["type"] == "Circle":
                painter.drawEllipse(shape["x"] - 25, shape["y"] - 25, 50, 50)
            elif shape["type"] == "Triangle":
                points = [
                    QPointF(shape["x"], shape["y"] - 25),
                    QPointF(shape["x"] - 25, shape["y"] + 25),
                    QPointF(shape["x"] + 25, shape["y"] + 25),
                ]
                painter.drawPolygon(QPolygonF(points))
            elif shape["type"] == "Pentagon":
                points = self.calculate_polygon_points(shape["x"], shape["y"], 25, 5)
                painter.drawPolygon(QPolygonF(points))
            elif shape["type"] == "Hexagon":
                points = self.calculate_polygon_points(shape["x"], shape["y"], 25, 6)
                painter.drawPolygon(QPolygonF(points))

            # Draw text
            painter.setPen(QPen(Qt.white))
            painter.drawText(shape["x"] - 20, shape["y"] + 5, shape["type"])

        # Draw connections
        painter.setPen(QPen(Qt.black, 2))
        for connection in self.shapes:
            if "connection" in connection:
                x1, y1 = connection["connection"]["start"]
                x2, y2 = connection["connection"]["end"]
                painter.drawLine(x1, y1, x2, y2)
                # Draw arrowhead
                arrow = QPolygonF()
                arrow.append(QPointF(x2, y2))
                arrow.append(QPointF(x2 - 10, y2 - 5))
                arrow.append(QPointF(x2 - 10, y2 + 5))
                painter.setBrush(QBrush(QColor("yellow")))
                painter.drawPolygon(arrow)

    def calculate_polygon_points(self, x, y, size, sides):
        """Calculate points for a regular polygon."""
        import math
        points = []
        for i in range(sides):
            angle = 2 * math.pi * i / sides
            px = x + size * math.cos(angle)
            py = y + size * math.sin(angle)
            points.append(QPointF(px, py))
        return points

    def mousePressEvent(self, event):
        """Handle mouse clicks."""
        if self.erase_mode:
            self.delete_shape_at(event.pos())
        elif self.connection_start:
            self.select_shape_for_connection(event.pos())
        else:
            self.draw_shape(event.pos())

    def draw_shape(self, pos):
        """Draw a shape at the clicked location."""
        shape_type = self.parent().shape_combo.currentText()
        color = self.parent().color_combo.currentText()
        x, y = pos.x(), pos.y()

        self.shapes.append({"type": shape_type, "color": color, "x": x, "y": y})
        self.update()

    def start_connection(self):
        """Start connecting shapes."""
        self.connection_start = None
        self.erase_mode = False

    def select_shape_for_connection(self, pos):
        """Select shapes to connect."""
        shape = self.find_shape_at(pos)
        if shape:
            if self.connection_start is None:
                self.connection_start = shape
            else:
                self.shapes.append(
                    {
                        "connection": {
                            "start": (self.connection_start["x"], self.connection_start["y"]),
                            "end": (shape["x"], shape["y"]),
                        }
                    }
                )
                self.connection_start = None
                self.update()

    def erase_shape(self):
        """Enable erase mode."""
        self.erase_mode = True
        self.connection_start = None

    def delete_shape_at(self, pos):
        """Delete the shape at the clicked location."""
        shape = self.find_shape_at(pos)
        if shape:
            self.shapes.remove(shape)
            self.update()

    def find_shape_at(self, pos):
        """Find the shape at the given coordinates."""
        for shape in self.shapes:
            if "type" in shape:
                x, y = shape["x"], shape["y"]
                if abs(pos.x() - x) <= 25 and abs(pos.y() - y) <= 25:
                    return shape
        return None


if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = ShapeDrawer()
    window.show()
    sys.exit(app.exec_())