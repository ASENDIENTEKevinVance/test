<?php

$db = new PDO("mysql:host=localhost;dbname=library_db", "root", "");
$in = json_decode(file_get_contents("php://input"), true);

if (isset($in['action'])) {
    //  LIST
    if ($in['action'] === 'list') {
        $stmt = $db->query("SELECT * FROM books");
        echo json_encode($stmt->fetchAll());
    }

    //  SEARCH
    if ($in['action'] === 'search') {
        $stmt = $db->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->execute([$in['isbn']]);
        echo json_encode($stmt->fetch());
    }

    //  ADD
    if ($in['action'] === 'add') {
        try {
            $stmt = $db->prepare("INSERT INTO books VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$in['isbn'], $in['title'], $in['copy'], $in['edit'], $in['price'], $in['qty']]);
            echo json_encode(["status" => "success"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "exists"]);
        }
    }

    //  EDIT
    if ($in['action'] === 'edit') {
        $stmt = $db->prepare("UPDATE books SET title=?, copyright=?, edition=?, price=?, quantity=? WHERE isbn=?");
        $stmt->execute([$in['title'], $in['copy'], $in['edit'], $in['price'], $in['qty'], $in['isbn']]);
        echo json_encode(["status" => "success"]);
    }

    //  DELETE
    if ($in['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM books WHERE isbn = ?");
        $stmt->execute([$in['isbn']]);
        echo json_encode(["status" => "success"]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: sans-serif; padding: 20px; }
    
    .main-container {
        display: flex;
        gap: 30px;
        margin-bottom: 20px;
    }

    .left-form {
        width: 350px;
    }
    .form-row {
        display: flex;
        margin-bottom: 8px;
    }
    .form-row label {
        width: 100px;
        font-weight: bold;
    }
    .form-row input {
        flex: 1;
        padding: 4px;
    }

    .right-controls {
        flex: 1;       
        display: flex;
        flex-direction: column;
    }

    .btn-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px; 
        margin-bottom: 20px;
    }
    button {
        padding: 15px;
        font-weight: bold;
        cursor: pointer;
    }

    #prompt {
        border: 2px solid black;
        background: #e0e0e0;
        padding: 20px;
        text-align: center;
        font-weight: bold;
        font-size: 1.2em;
        min-height: 40px;
    }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #333; padding: 8px; text-align: center; }
    th { background: #ddd; }
</style>
</head>

<body>

    <div class="main-container">
        
        <div class="left-form">
            <div class="form-row"><label>ISBN #:</label> <input id="isbn"></div>
            <div class="form-row"><label>Title:</label> <input id="title"></div>
            <div class="form-row"><label>Copyright:</label> <input id="copy"></div>
            <div class="form-row"><label>Edition:</label> <input id="edit"></div>
            <div class="form-row"><label>Price:</label> <input id="price" type="number"></div>
            <div class="form-row"><label>Quantity:</label> <input id="qty" type="number"></div>
        </div>

        <div class="right-controls">
            
            <div class="btn-grid">
                <button onclick="doSearch()">SEARCH</button>
                <button onclick="doEdit()">EDIT</button>
                <button onclick="doDelete()">DELETE</button>
                <button onclick="doAdd()">ADD</button>
            </div>

            <div id="prompt"></div>
        </div>

    </div>

    <hr> <table>
        <thead>
            <tr>
                <th>ISBN</th><th>Title</th><th>Copyright</th><th>Edition</th><th>Price</th><th>Quantity</th><th>TOTAL</th>
            </tr>
        </thead>
        <tbody id="list"></tbody>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                <td id="totalQty" style="font-weight:bold;">0</td>
                <td id="grandTotal" style="font-weight:bold;">0.00</td>
            </tr>
        </tfoot>
    </table>
    <button onclick="clearForm();setPrompt('')" style="padding:30px;margin-top:20px;float:right">Clear Form</button>

    <script>
        function clearForm() {
            document.querySelectorAll('input').forEach(box => box.value = '');
        }

        function setPrompt(msg) { document.getElementById('prompt').innerText = msg; }

        function getForm() {
            return {
                isbn: document.getElementById('isbn').value,
                title: document.getElementById('title').value,
                copy: document.getElementById('copy').value,
                edit: document.getElementById('edit').value,
                price: document.getElementById('price').value,
                qty: document.getElementById('qty').value
            };
        }

        //  ADD
        async function doAdd() {
            const data = getForm();
            if(!data.isbn) { setPrompt("NO RECORD TO ADD"); return; } 

            const res = await fetch('index.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'add', ...data })
            });
            const result = await res.json();

            if (result.status === 'exists') {
                setPrompt("RECORD ALREADY EXISTS");
                document.getElementById('isbn').focus();
            } else {
                setPrompt("RECORD SUCCESSFULLY SAVED");
                clearForm(); 
                loadList();
                document.getElementById('isbn').focus();
            }
        }

        //  SEARCH
        async function doSearch() {
            const isbn = document.getElementById('isbn').value;
            const res = await fetch('index.php', {
                method: 'POST', body: JSON.stringify({ action: 'search', isbn: isbn })
            });
            const row = await res.json();

            if (row) {
                document.getElementById('title').value = row.title;
                document.getElementById('copy').value = row.copyright;
                document.getElementById('edit').value = row.edition;
                document.getElementById('price').value = row.price;
                document.getElementById('qty').value = row.quantity;
                setPrompt("ITEM IS FOUND");
                document.getElementById('isbn').focus();
            } else {
                setPrompt("ITEM NOT FOUND");
                document.getElementById('isbn').focus();
            }
        }

        //  EDIT
        async function doEdit() {
            const data = getForm();
            if(!data.isbn) { setPrompt("NO RECORD TO EDIT"); return; }

            await fetch('index.php', {
                method: 'POST', body: JSON.stringify({ action: 'edit', ...data })
            });
            setPrompt("RECORD SUCCESSFULLY UPDATED");
            clearForm(); 
            loadList();
        }

        //  DELETE
        async function doDelete() {
            const isbn = document.getElementById('isbn').value;
            await fetch('index.php', {
                method: 'POST', body: JSON.stringify({ action: 'delete', isbn: isbn })
            });
            setPrompt("RECORD SUCCESSFULLY DELETED");
            clearForm(); 
            loadList();
            document.getElementById('isbn').focus();
        }

        //  LOAD LIST
        async function loadList() {
            const res = await fetch('index.php', { method: 'POST', body: JSON.stringify({ action: 'list' }) });
            const data = await res.json();
            
            let html = "";
            let grandTotal = 0;
            let totalQty = 0;
            
            data.forEach(row => {
                let qty = Number(row.quantity); 
                let price = Number(row.price);
                
                let rowTotal = price * qty;
                
                grandTotal += rowTotal;
                totalQty += qty; 

                html += `<tr>
                    <td>${row.isbn}</td>
                    <td>${row.title}</td>
                    <td>${row.copyright}</td>
                    <td>${row.edition}</td>
                    <td>${row.price}</td>
                    <td>${qty}</td>  
                    <td>${rowTotal.toFixed(2)}</td>
                </tr>`;
            });
            
            document.getElementById('list').innerHTML = html;
            
            // Totals
            document.getElementById('totalQty').innerText = totalQty;
            document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
        }

        loadList();
    </script>
</body>
</html>
