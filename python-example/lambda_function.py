from PIL import Image
import boto3
from os import environ

def add_watermark(input_image_path, output_image_path, watermark_image_path, position):
    base_image = Image.open(input_image_path)
    watermark = Image.open(watermark_image_path)

    # add watermark to your image
    base_image.paste(watermark, position)
    base_image.show()
    base_image.save(output_image_path)

def python_add_watermark(event, context):

    print(event)
    print(context)

    s3 = boto3.client(
        's3',
        aws_access_key_id=environ['public_key'],
        aws_secret_access_key=environ['secret_key']
    )

    bucket_name = 'custom-lambda-php'
    image_name = 'gotW.jpg'

    s3.download_file(bucket_name, 'origin/gotW.jpg', image_name)
    add_watermark(image_name, image_name, 'stp_logo.png', position=(10, 10))

    s3.upload_file(image_name, bucket_name, "watermark/python_{}".format(image_name))

