import urllib.request
import urllib.parse
import json

boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW'
body = (
    b'--' + boundary.encode('utf-8') + b'\r\n'
    b'Content-Disposition: form-data; name="ktp_image"; filename="dummy.jpg"\r\n'
    b'Content-Type: image/jpeg\r\n\r\n'
    b'\xff\xd8\xff\xe0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00\xff\xdb\x00C\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\t\t\x08\n\x0c\x14\r\x0c\x0b\x0b\x0c\x19\x12\x13\x0f\x14\x1d\x1a\x1f\x1e\x1d\x1a\x1c\x1c $.\' ",#\x1c\x1c(7),01444\x1f\'9=82<.342\xff\xc0\x00\x0b\x08\x00\x01\x00\x01\x01\x01\x11\x00\xff\xc4\x00\x1f\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\t\n\x0b\xff\xda\x00\x08\x01\x01\x00\x00?\x00\x00\r\n'
    b'--' + boundary.encode('utf-8') + b'--\r\n'
)

req = urllib.request.Request(
    'http://127.0.0.1:8000/extract-ktp',
    data=body,
    headers={'Content-Type': f'multipart/form-data; boundary={boundary}'}
)
try:
    with urllib.request.urlopen(req) as response:
        print('STATUS CODE:', response.getcode())
        print('RESPONSE:', response.read().decode('utf-8'))
except urllib.error.HTTPError as e:
    print('STATUS CODE:', e.code)
    print('RESPONSE:', e.read().decode('utf-8'))
except Exception as e:
    print('ERROR:', e)
