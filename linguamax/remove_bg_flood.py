from PIL import Image

def remove_bg_floodfill(input_path, output_path, tolerance=15):
    img = Image.open(input_path).convert("RGBA")
    width, height = img.size
    pixels = img.load()
    
    # Target color is the top-left pixel
    target_color = pixels[0, 0]
    
    # Stack for flood fill
    stack = [(0, 0)]
    visited = set()
    
    def color_distance(c1, c2):
        return sum(abs(c1[i] - c2[i]) for i in range(3))
        
    while stack:
        x, y = stack.pop()
        
        if (x, y) in visited:
            continue
        visited.add((x, y))
        
        if color_distance(pixels[x, y], target_color) <= tolerance * 3:
            # Set to transparent
            pixels[x, y] = (255, 255, 255, 0)
            
            # Add neighbors
            if x > 0: stack.append((x - 1, y))
            if x < width - 1: stack.append((x + 1, y))
            if y > 0: stack.append((x, y - 1))
            if y < height - 1: stack.append((x, y + 1))

    img.save(output_path, "PNG")

remove_bg_floodfill("assets/img/user_mascot.png", "assets/img/user_mascot_transparent.png")
print("Done")
