import time
import sys
import json
import urllib.request

# ====================================================================
# SCRIPT TESTING SENSOR RFID RC522 RASPBERRY PI
# Absen TI - Politeknik Negeri Padang
# ====================================================================

# ALAMAT IP LAPTOP / SERVER LARAVEL (192.168.1.9)
LAPTOP_IP = "192.168.1.9"  # IP Laptop Server Laravel Anda
SERVER_PORT = "8000"

# Target Endpoint API Laravel
SERVER_URL = f"http://{LAPTOP_IP}:{SERVER_PORT}/api/iot/verify"

HAS_HARDWARE = False
reader = None

try:
    from mfrc522 import SimpleMFRC522
    reader = SimpleMFRC522()
    HAS_HARDWARE = True
    print("✅ Hardware Sensor RFID RC522 Raspberry Pi terdeteksi!")
except Exception:
    print("ℹ️ Library mfrc522 tidak terdeteksi. Berjalan dalam mode simulasi.")

print("\n" + "=" * 60)
print("📡 SCRIPT TESTING SENSOR RFID RC522 (RASPBERRY PI -> LARAVEL)")
print(f"🔗 Target Server: {SERVER_URL}")
print("=" * 60)

def send_rfid_to_server(uid):
    print(f"\n💳 [TAP] Kartu RFID Terbaca! Kode UID: {uid}")
    print(f"📤 Mengirimkan payload UID ke Laptop Server ({LAPTOP_IP})...")
    
    payload = json.dumps({"rfid_uid": str(uid).strip()}).encode('utf-8')
    req = urllib.request.Request(
        SERVER_URL, 
        data=payload, 
        headers={'Content-Type': 'application/json', 'Accept': 'application/json'}
    )
    
    try:
        with urllib.request.urlopen(req, timeout=5) as response:
            res_code = response.status
            res_body = response.read().decode('utf-8')
            print(f"✅ Status [{res_code}]: Berhasil terkirim ke Server Laravel!")
            print(f"📄 Respon Server: {res_body}")
    except Exception as e:
        print(f"❌ Gagal Terhubung ke Laptop Server ({LAPTOP_IP}): {e}")

if __name__ == "__main__":
    try:
        if HAS_HARDWARE:
            print("\nSilakan tempelkan (tap) kartu RFID Anda pada scanner RC522...")
            while True:
                id_card, text = reader.read()
                uid_str = str(id_card).strip()
                if uid_str:
                    send_rfid_to_server(uid_str)
                    time.sleep(2)
        else:
            test_uid = sys.argv[1] if len(sys.argv) > 1 else "CF45B1E6DD"
            send_rfid_to_server(test_uid)

    except KeyboardInterrupt:
        print("\n\nTesting Selesai.")
        sys.exit(0)
