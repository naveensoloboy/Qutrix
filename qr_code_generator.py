import qrcode
from PIL import Image, ImageDraw, ImageFont

# ===== SETTINGS =====
link = "https://qutrix.onrender.com/feedbackform.html"   # Replace with your link
title = "FeedBack QR"        # Change to "Feedback QR" etc.
title_position = "bottom"           # "top" or "bottom"

# ===== GENERATE QR =====
qr = qrcode.QRCode(
    version=1,
    box_size=10,
    border=4
)

qr.add_data(link)
qr.make(fit=True)

qr_img = qr.make_image(fill_color="black", back_color="white")
qr_img = qr_img.convert("RGB")

# ===== FONT =====
font = ImageFont.load_default()

# ===== GET TEXT SIZE =====
dummy_img = Image.new("RGB", (1, 1))
draw = ImageDraw.Draw(dummy_img)

bbox = draw.textbbox((0, 0), title, font=font)
text_width = bbox[2] - bbox[0]
text_height = bbox[3] - bbox[1]

# ===== CREATE FINAL IMAGE =====
padding = 20

new_width = max(qr_img.width, text_width + 20)
new_height = qr_img.height + text_height + padding * 2

final_img = Image.new("RGB", (new_width, new_height), "white")
draw = ImageDraw.Draw(final_img)

# Center positions
text_x = (new_width - text_width) // 2
qr_x = (new_width - qr_img.width) // 2

if title_position.lower() == "top":
    draw.text((text_x, 10), title, fill="black", font=font)
    final_img.paste(qr_img, (qr_x, text_height + padding))
else:
    final_img.paste(qr_img, (qr_x, 10))
    draw.text(
        (text_x, qr_img.height + padding),
        title,
        fill="black",
        font=font
    )

# ===== SAVE =====
final_img.save("feedback_qr.png")

print("QR saved as qr_with_title.png")