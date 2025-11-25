from flask import Flask, render_template, request, redirect, url_for
import sqlite3
import os
from datetime import datetime

app = Flask(__name__)

def db_connection():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    db_path = os.path.join(base_dir, 'clinic.db')
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    return conn

@app.route('/')
def index():
    return render_template('index.html')

# --- DOCTORS ---
@app.route('/doctors', methods=['GET', 'POST'])
def doctors():
    conn = db_connection()
    if request.method == 'POST':
        conn.execute('INSERT INTO doctors (docFName, docLName, docAddress, docSpecial) VALUES (?, ?, ?, ?)',
                     (request.form['fname'], request.form['lname'], request.form['address'], request.form['special']))
        conn.commit()
        return redirect(url_for('doctors'))
    
    doctors_list = conn.execute('SELECT * FROM doctors WHERE docDel = 0').fetchall()
    count = len(doctors_list)
    conn.close()
    return render_template('doctors.html', doctors=doctors_list, count=count)

@app.route('/doctors/edit/<int:id>', methods=['GET', 'POST'])
def edit_doctor(id):
    conn = db_connection()
    if request.method == 'POST':
        conn.execute('UPDATE doctors SET docFName=?, docLName=?, docAddress=?, docSpecial=? WHERE docID=?',
                     (request.form['fname'], request.form['lname'], request.form['address'], request.form['special'], id))
        conn.commit()
        conn.close()
        return redirect(url_for('doctors'))
        
    doctor = conn.execute('SELECT * FROM doctors WHERE docID = ?', (id,)).fetchone()
    conn.close()
    return render_template('edit_doctor.html', doctor=doctor)

@app.route('/doctors/delete/<int:id>')
def delete_doctor(id):
    conn = db_connection()
    conn.execute('UPDATE doctors SET docDel = 1 WHERE docID = ?', (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('doctors'))

# --- PATIENTS ---
@app.route('/patients', methods=['GET', 'POST'])
def patients():
    conn = db_connection()
    if request.method == 'POST':
        conn.execute('INSERT INTO patients (patFName, patLName, patBDate, patTelNo) VALUES (?, ?, ?, ?)',
                     (request.form['fname'], request.form['lname'], request.form['bdate'], request.form['tel']))
        conn.commit()
        return redirect(url_for('patients'))

    patients_list = conn.execute('SELECT * FROM patients WHERE patDel = 0').fetchall()
    count = len(patients_list)
    conn.close()
    return render_template('patients.html', patients=patients_list, count=count)

@app.route('/patients/edit/<int:id>', methods=['GET', 'POST'])
def edit_patient(id):
    conn = db_connection()
    if request.method == 'POST':
        conn.execute('UPDATE patients SET patFName=?, patLName=?, patBDate=?, patTelNo=? WHERE patID=?',
                     (request.form['fname'], request.form['lname'], request.form['bdate'], request.form['tel'], id))
        conn.commit()
        conn.close()
        return redirect(url_for('patients'))
        
    patient = conn.execute('SELECT * FROM patients WHERE patID = ?', (id,)).fetchone()
    conn.close()
    return render_template('edit_patient.html', patient=patient)

@app.route('/patients/delete/<int:id>')
def delete_patient(id):
    conn = db_connection()
    conn.execute('UPDATE patients SET patDel = 1 WHERE patID = ?', (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('patients'))

# --- CONSULTATIONS ---
@app.route('/consultations', methods=['GET', 'POST'])
def consultations():
    conn = db_connection()
    if request.method == 'POST':
        current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        conn.execute('INSERT INTO consultations (patID, docID, consultDate, diagnosis, prescription) VALUES (?, ?, ?, ?, ?)',
                     (request.form['patID'], request.form['docID'], current_time, request.form['diagnosis'], request.form['prescription']))
        conn.commit()
        return redirect(url_for('consultations'))

    docs = conn.execute('SELECT docID, docFName, docLName FROM doctors WHERE docDel = 0').fetchall()
    pats = conn.execute('SELECT patID, patFName, patLName FROM patients WHERE patDel = 0').fetchall()
    consults = conn.execute('''
        SELECT c.*, p.patFName, p.patLName, d.docFName, d.docLName 
        FROM consultations c
        JOIN patients p ON c.patID = p.patID
        JOIN doctors d ON c.docID = d.docID
        WHERE c.consultDel = 0
        ORDER BY c.consultDate DESC
    ''').fetchall()
    
    count = len(consults)
    conn.close()
    return render_template('consultations.html', consultations=consults, doctors=docs, patients=pats, count=count)

@app.route('/consultations/edit/<int:id>', methods=['GET', 'POST'])
def edit_consultation(id):
    conn = db_connection()
    if request.method == 'POST':
        conn.execute('''UPDATE consultations SET patID=?, docID=?, diagnosis=?, prescription=? WHERE consultID=?''',
                     (request.form['patID'], request.form['docID'], request.form['diagnosis'], request.form['prescription'], id))
        conn.commit()
        conn.close()
        return redirect(url_for('consultations'))
        
    consultation = conn.execute('SELECT * FROM consultations WHERE consultID = ?', (id,)).fetchone()
    docs = conn.execute('SELECT docID, docFName, docLName FROM doctors WHERE docDel = 0').fetchall()
    pats = conn.execute('SELECT patID, patFName, patLName FROM patients WHERE patDel = 0').fetchall()
    conn.close()
    return render_template('edit_consultation.html', consultation=consultation, doctors=docs, patients=pats)

@app.route('/consultations/delete/<int:id>')
def delete_consultation(id):
    conn = db_connection()
    conn.execute('UPDATE consultations SET consultDel = 1 WHERE consultID = ?', (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('consultations'))

# --- INQUIRIES ---
@app.route('/inquiries', methods=['GET', 'POST'])
def inquiries():
    conn = db_connection()
    results = []
    search_type = ""
    count = 0

    if request.method == 'POST':
        search_type = request.form['search_type']
        if search_type == 'specialization':
            results = conn.execute("SELECT * FROM doctors WHERE docSpecial LIKE ? AND docDel=0", ('%' + request.form['keyword'] + '%',)).fetchall()
        elif search_type == 'age_range':
            current_year = datetime.now().year
            start_date = f"{current_year - int(request.form['max_age'])}-01-01"
            end_date = f"{current_year - int(request.form['min_age'])}-12-31"
            results = conn.execute("SELECT * FROM patients WHERE patBDate BETWEEN ? AND ? AND patDel=0", (start_date, end_date)).fetchall()
        elif search_type == 'by_id':
            sid = request.form['search_id']
            results = conn.execute('''SELECT c.*, p.patFName, p.patLName, d.docFName, d.docLName 
                                      FROM consultations c JOIN patients p ON c.patID=p.patID JOIN doctors d ON c.docID=d.docID 
                                      WHERE (c.patID=? OR c.docID=?) AND c.consultDel=0''', (sid, sid)).fetchall()
        elif search_type == 'date_range':
            results = conn.execute('''SELECT c.*, p.patFName, p.patLName, d.docFName, d.docLName 
                                      FROM consultations c JOIN patients p ON c.patID=p.patID JOIN doctors d ON c.docID=d.docID 
                                      WHERE c.consultDate BETWEEN ? AND ? AND c.consultDel=0''', 
                                      (request.form['start_date'], request.form['end_date'] + ' 23:59:59')).fetchall()
    if results: count = len(results)
    conn.close()
    return render_template('inquiries.html', results=results, search_type=search_type, count=count)

if __name__ == '__main__':
    app.run(debug=True)