import mysql.connector

print("Imported successfully!")

conexion = mysql.connector.connect(
    host="localhost",
    user="root",
    password="root",
    database="reforestacion"
)

print("Connected to DB!")
conexion.close()
