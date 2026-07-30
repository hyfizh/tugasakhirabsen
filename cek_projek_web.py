import os
import requests

# Warna untuk output terminal
GREEN = "\033[92m"
RED = "\033[91m"
YELLOW = "\033[93m"
CYAN = "\033[96m"
RESET = "\033[0m"

print(f"{CYAN}==================================================")
print("🔍 AUDIT KELENGKAPAN PROGRAM WEB ABSENSI IOT")
print(f"=================================================={RESET}\n")

# 1. CEK FILE DAN STRUKTUR KODE
print(f"{YELLOW}[1/2] Memeriksa Komponen File & Kode Web...{RESET}")

required_files = {
    "Route API": "routes/api.php",
    "Database Migration": "database/migrations",
    "Model Mahasiswa": ["app/Models/Mahasiswa.php", "app/Mahasiswa.php"],
    "Model Absensi": ["app/Models/Absensi.php", "app/Absensi.php"],
    "Controller API Absensi": [
        "app/Http/Controllers/Api/AbsensiController.php",
        "app/Http/Controllers/AbsensiController.php"
    ]
}

for component, path in required_files.items():
    if isinstance(path, list):
        found = any(os.path.exists(p) for p in path)
        status_path = next((p for p in path if os.path.exists(p)), path[0])
    else:
        found = os.path.exists(path)
        status_path = path

    if found:
        print(f"  ✅ {component:<30} : {GREEN}ADA{RESET} ({status_path})")
    else:
        print(f"  ❌ {component:<30} : {RED}BELUM ADA{RESET}")

print("\n" + "-" * 50 + "\n")

# 2. CEK KONEKSI SERVER LOCAL & API ENDPOINT
print(f"{YELLOW}[2/2] Memeriksa Respon Web Server Local...{RESET}")

BASE_URL = "http://127.0.0.1:8000"
API_ENDPOINT = f"{BASE_URL}/api/iot/absen"

try:
    response = requests.get(BASE_URL, timeout=3)
    print(f"  ✅ Web Server Local ({BASE_URL}) : {GREEN}ONLINE / AKTIF{RESET}")
except requests.exceptions.ConnectionError:
    print(f"  ❌ Web Server Local ({BASE_URL}) : {RED}OFFLINE{RESET}")
    print(f"     {YELLOW}💡 Jalankan 'php artisan serve' terlebih dahulu!{RESET}")
    exit()

print(f"\n  🧪 Menguji Kirim Payload Dummy ke Endpoint: {CYAN}{API_ENDPOINT}{RESET}")

# Header WAJIB agar Laravel merespon format JSON
headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
}

# Payload Dummy
dummy_payload = {
    "uid_rfid": "CF45B1E6DD",
    "rfid": "CF45B1E6DD"
}

try:
    api_response = requests.post(API_ENDPOINT, json=dummy_payload, headers=headers, timeout=5)
    
    print(f"  📡 HTTP Status Code: {CYAN}{api_response.status_code}{RESET}")
    print(f"  📄 Response JSON dari Server:")
    try:
        json_data = api_response.json()
        print(f"     {GREEN}{json_data}{RESET}")
    except Exception:
        print(f"     {RED}{api_response.text[:300]}{RESET}")

    if api_response.status_code in [200, 201]:
        print(f"\n  🎉 {GREEN}SISTEM API TERHUBUNG DENGAN PERFECT!{RESET}")
    elif api_response.status_code in [400, 404, 422]:
        print(f"\n  ℹ️ {YELLOW}API BISA DIAKSES TAPI MEMBUTAHPESAN VALIDASI (Sangat Normal jika data/UID belum ada di DB).{RESET}")

except Exception as e:
    print(f"  ❌ Gagal menghubungi API Endpoint: {e}")

print(f"\n{CYAN}==================================================")
print("AUDIT SELESAI")
print(f"=================================================={RESET}")