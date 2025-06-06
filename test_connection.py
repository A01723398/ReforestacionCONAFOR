import mysql.connector
from mysql.connector import Error

with open("debug_output.txt", "w") as f:
    f.write("Python script ran successfully!\n")

print("Trying to connect...", flush=True)

try:
    connection = mysql.connector.connect(
        host="localhost",
        user="root",
        password="root",
        database="reforestacion",
        port=3306
    )

    if connection.is_connected():
        print("✅ Connection successful!", flush=True)
        cursor = connection.cursor()
        cursor.execute("SHOW TABLES;")
        for (table_name,) in cursor.fetchall():
            print(f"Table: {table_name}", flush=True)

        cursor.close()
        connection.close()
    else:
        print("❌ Connection failed.", flush=True)

except Error as e:
    print(f"❌ Error connecting: {e}", flush=True)
