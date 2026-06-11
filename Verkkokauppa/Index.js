function showPage(pageId) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(pageId).classList.add('active');
    if (pageId === 'koti') {
        fetch("Index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "koti=active"
        })
        .then(r => r.text())
        .then(data => { 
            console.log("PHP response:", data);
            return data;
        })
        .then(data => {
            document.getElementById("koti").innerHTML = data;
        });
    } else if (pageId === 'hallinta') {
        hallinta();
    }
}

function showData(dataId) {
    fetch("Index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: dataId
    })
    .then(r => r.text())
    .then(data => {
        document.getElementById("hallinta").innerHTML = data;
    });
}

function sendForm(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);

    fetch("Index.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.text())
    .then(data => {
        document.getElementById("hallinta").innerHTML = data;
    });
}

function hallinta() {
    document.getElementById("hallinta").innerHTML = `
        <button class="hallinta" onclick="showData('toiminto=l_t')">Lisää uusi tuote</button>
        <button class="hallinta" onclick="showData('toiminto=l_tr')">Lisää uusi tuoteryhmä</button>
        <br><br>
        <button class="hallinta" onclick="showData('toiminto=m_t')">Muuta tuotteita</button>
        <button class="hallinta" onclick="showData('toiminto=m_tr')">Muuta tuoteryhmiä</button>
        <br><br>
        <button class="hallinta" onclick="showData('toiminto=t_t')">Tarkastele tuotteita</button>
        <button class="hallinta" onclick="showData('toiminto=t_tr')">Tarkastele tuoteryhmiä</button>
    `;
}