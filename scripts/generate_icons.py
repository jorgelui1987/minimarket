"""Genera iconos PNG para la PWA (icon-192.png y icon-512.png).

Dibuja un carrito de compras blanco sobre fondo verde esmeralda (#047857).
Se renderiza a 4x para suavizado (anti-aliasing) y se reduce con LANCZOS.
"""
from PIL import Image, ImageDraw

BG = (4, 120, 87)          # #047857 emerald-700
WHITE = (255, 255, 255)
CORNER_RATIO = 0.20        # radio de esquinas 20% (rx=40/192 en el SVG)


def draw_cart(draw, size):
    """Dibuja un carrito de compras en coordenadas 0-100 dentro del icono."""
    s = size / 100.0

    def P(x, y):
        return (x * s, y * s)

    # Mango del carrito (líneas)
    draw.line([P(38, 32), P(34, 18)], fill=WHITE, width=int(4 * s))
    draw.line([P(62, 32), P(66, 18)], fill=WHITE, width=int(4 * s))
    draw.line([P(34, 18), P(66, 18)], fill=WHITE, width=int(5 * s))

    # Canasta superior (paralelogramo)
    draw.polygon([
        P(36, 34), P(64, 34), P(66, 44), P(34, 44)
    ], fill=WHITE)

    # Canasta inferior (trapecio)
    draw.polygon([
        P(34, 44), P(66, 44), P(61, 68), P(39, 68)
    ], fill=WHITE)

    # Ruedas (círculos)
    draw.ellipse([P(36, 66), P(46, 76)], fill=WHITE)
    draw.ellipse([P(54, 66), P(64, 76)], fill=WHITE)


def make_icon(size, path):
    scale = 4
    big = size * scale
    img = Image.new("RGBA", (big, big), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)

    # Fondo con esquinas redondeadas
    radius = int(big * CORNER_RATIO)
    draw.rounded_rectangle([0, 0, big - 1, big - 1], radius=radius, fill=BG)

    # Carrito centrado
    draw_cart(draw, big)

    # Reducir con suavizado
    img = img.resize((size, size), Image.LANCZOS)
    img.save(path, "PNG")
    print(f"Generado: {path} ({size}x{size})")


if __name__ == "__main__":
    make_icon(192, "public/icons/icon-192.png")
    make_icon(512, "public/icons/icon-512.png")