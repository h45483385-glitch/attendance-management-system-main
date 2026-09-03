import requests

# Your Laravel API URL
url = "http://127.0.0.1:8000/api/face-attendance"

# Data to send
data = {
    "emp_id": 111
}

try:
    response = requests.post(url, json=data)

    print("Status Code:", response.status_code)
    print("Response:", response.json())

except Exception as e:
    print("Error:", str(e))