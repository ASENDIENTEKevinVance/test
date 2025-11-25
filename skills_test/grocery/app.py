from flask import Flask, render_template, redirect, url_for, request
import os
import sqlite3
from datetime import datetime

app = Flask(__name__)

def db_connect():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    db_path = os.path.join(base_dir, 'grocery.db')
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    conn = db_connect()
    conn.execute('''
        CREATE TABLE IF NOT EXISTS grocery (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            is_deleted INTEGER DEFAULT 0
        )
    ''')
    conn.commit()
    conn.close()

init_db()

@app.route('/')
def index():
    conn = db_connect()
    grocery = conn.execute('SELECT * FROM grocery WHERE is_deleted = 0').fetchall()
    conn.close()

    grocery_count = len(grocery)

    return render_template('index.html', grocery = grocery, grocery_count = grocery_count)

@app.route('/add', methods=('POST',))
def add():
    name = request.form['name']
    conn = db_connect()
    conn.execute('INSERT INTO grocery (name) VALUES (?)',(name,))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

@app.route('/delete/<int:id>')
def delete(id):
    conn = db_connect()
    conn.execute('UPDATE grocery SET is_deleted = 1 WHERE id = ?', (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

if __name__ == '__main__':
    app.run(debug=True)