import sqlite3
import os

def db_connection():
    # Force the database to be created in the same directory as this script
    base_dir = os.path.dirname(os.path.abspath(__file__))
    db_path = os.path.join(base_dir, 'clinic.db')
    conn = sqlite3.connect(db_path)
    return conn

def init_db():
    conn = db_connection()
    # Enable Foreign Key support
    conn.execute("PRAGMA foreign_keys = ON;")
    
    conn.executescript('''
        -- Table 1: Doctors
        CREATE TABLE IF NOT EXISTS doctors (
            docID INTEGER PRIMARY KEY AUTOINCREMENT,
            docFName TEXT NOT NULL,
            docLName TEXT NOT NULL,
            docAddress TEXT NOT NULL,
            docSpecial TEXT NOT NULL,
            docDel INTEGER DEFAULT 0
        );

        -- Table 2: Patients
        CREATE TABLE IF NOT EXISTS patients (
            patID INTEGER PRIMARY KEY AUTOINCREMENT,
            patFName TEXT NOT NULL,
            patLName TEXT NOT NULL,
            patBDate TEXT NOT NULL,
            patTelNo TEXT NOT NULL,
            patDel INTEGER DEFAULT 0
        );

        -- Table 3: Consultations
        CREATE TABLE IF NOT EXISTS consultations (
            consultID INTEGER PRIMARY KEY AUTOINCREMENT,
            patID INTEGER NOT NULL,
            docID INTEGER NOT NULL,
            consultDate TEXT NOT NULL,
            diagnosis TEXT NOT NULL,
            prescription TEXT NOT NULL,
            consultDel INTEGER DEFAULT 0,
            FOREIGN KEY (patID) REFERENCES patients (patID),
            FOREIGN KEY (docID) REFERENCES doctors (docID)
        );
    ''')
    
    conn.commit()
    conn.close()
    print("Database 'clinic.db' created successfully.")

if __name__ == '__main__':
    init_db()