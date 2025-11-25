from flask import Flask, render_template, request, redirect, url_for
import sqlite3
import os

app = Flask(__name__)

# Database Setup
def get_db_connection():
    # Gets the path of the current script to force database location
    base_dir = os.path.dirname(os.path.abspath(__file__))
    db_path = os.path.join(base_dir, 'students.db')
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row  # Allows accessing columns by name
    return conn

def init_db():
    conn = get_db_connection()
    # Create table with soft delete column
    conn.execute('''
        CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            course TEXT NOT NULL,
            is_deleted INTEGER DEFAULT 0
        )
    ''')
    conn.commit()
    conn.close()

# Run DB setup immediately
init_db()

# --- ROUTES (The URL links) ---

@app.route('/')
def index():
    conn = get_db_connection()
    # R - Read: Only select active students (Soft Delete Logic)
    students = conn.execute('SELECT * FROM students WHERE is_deleted = 0').fetchall()
    conn.close()
    
    # Calculate the number of active students
    student_count = len(students)
    
    return render_template('index.html', students=students, student_count=student_count)

@app.route('/add', methods=('POST',))
def add():
    # C - Create
    name = request.form['name']
    course = request.form['course']
    conn = get_db_connection()
    conn.execute('INSERT INTO students (name, course) VALUES (?, ?)', (name, course))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

@app.route('/edit/<int:id>', methods=('GET', 'POST'))
def edit(id):
    # U - Update
    conn = get_db_connection()
    if request.method == 'POST':
        name = request.form['name']
        course = request.form['course']
        conn.execute('UPDATE students SET name = ?, course = ? WHERE id = ?', (name, course, id))
        conn.commit()
        conn.close()
        return redirect(url_for('index'))
    
    student = conn.execute('SELECT * FROM students WHERE id = ?', (id,)).fetchone()
    conn.close()
    return render_template('edit.html', student=student)

@app.route('/delete/<int:id>')
def delete(id):
    # D - Soft Delete: We update, we do NOT delete!
    conn = get_db_connection()
    conn.execute('UPDATE students SET is_deleted = 1 WHERE id = ?', (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

if __name__ == '__main__':
    app.run(debug=True)