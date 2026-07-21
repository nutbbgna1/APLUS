from PIL import Image, ImageDraw
import sys

def remove_background(input_path, output_path):
    try:
        img = Image.open(input_path).convert("RGBA")
        
        # We will floodfill from (0,0)
        # However, ImageDraw.floodfill modifies the image in place.
        # But wait, floodfill doesn't have a tolerance parameter in some older PIL versions.
        # Let's check the background color
        bg_color = img.getpixel((0,0))
        
        # Create a mask using BFS (flood fill)
        width, height = img.size
        pixels = img.load()
        
        visited = set()
        queue = [(0, 0), (width-1, 0), (0, height-1), (width-1, height-1)]
        
        def color_diff(c1, c2):
            return abs(c1[0]-c2[0]) + abs(c1[1]-c2[1]) + abs(c1[2]-c2[2])
            
        while queue:
            x, y = queue.pop(0)
            if (x, y) in visited:
                continue
            visited.add((x, y))
            
            c = pixels[x, y]
            if color_diff(c, bg_color) < 30: # tolerance
                pixels[x, y] = (255, 255, 255, 0)
                
                # add neighbors
                for dx, dy in [(0,1), (1,0), (0,-1), (-1,0)]:
                    nx, ny = x+dx, y+dy
                    if 0 <= nx < width and 0 <= ny < height:
                        if (nx, ny) not in visited:
                            queue.append((nx, ny))
                            
        img.save(output_path)
        print("Success")
    except Exception as e:
        print("Error:", e)

remove_background("assets/img/hero_laptop.png", "assets/img/hero_laptop_transparent.png")
