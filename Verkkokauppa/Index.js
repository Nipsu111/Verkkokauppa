function showPage(pageId) {
    closeModal();
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(pageId).classList.add('active');
    if (pageId === 'koti') {
        fetch_page(pageId, "active");
        search.style.display = "block";
    } else if (pageId === 'hallinta') {
        hallinta();
        search.style.display = "none";
    } else if (pageId === 'osto') {
        ostoskori();
        search.style.display = "none";
    } else if (pageId === 'haku') {
        event.preventDefault();
        fetch_page(pageId, event.target.haku.value);
    }
}

function fetch_page(pageId, pageData) {
    fetch("Index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `${pageId}=${pageData}`
    })
    .then(r => r.text())
    .then(data => { 
        return data;
    })
    .then(data => {
        document.getElementById(`${pageId}`).innerHTML = data;
    });
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

function showModal(kuva, id, nimi, hinta, veroton, myynti, vero, varastossa) {
    var min = 1;
    var max = 100000;
    var placeholder = "Paino";
    var kappale = "(g)";

    if (myynti != "kg") {
        var placeholder = "Määrä";
        var kappale = "(kpl)";
    }
    modal.style.display = "block";
    document.getElementById("modal").innerHTML = `
        <div class="modal-content">
            <span class="close" onclick='closeModal()'>&times;</span>
            <img src= ${kuva}>
            <p>Tuote: ${nimi} </p>
            <p>Hinta: ${hinta}€/${myynti}</p>
            <p>Tuotetta varastossa: ${varastossa} ${kappale}</p>
            <div style='display:flex; align-items:center; gap:10px;'>
                <label for='maara'>${placeholder} ${kappale}:</label>
                <input type='number' id='maara' min='${min}' max='${max}' name='maara' placeholder='${placeholder}...'>
            </div> <br>
            <button type="button" class="osto" onclick='osto("${id}", "${nimi}", "${hinta}", "${veroton}", "${myynti}", "${vero}", "${varastossa}", "${kuva}"), closeModal()'>Lisää ostoskoriin</button>
        </div>
    `;
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
})

function closeModal() {
    modal.style.display = "none";
}

function osto(id, nimi, hinta, veroton, myynti, vero, varastossa, kuva) {
    let tuote_lista = JSON.parse(localStorage.getItem('tuote_lista'));
    var maara = document.getElementById("maara").value;
    if (maara != null && maara != '' && maara > 0) {
        if (parseInt(maara) > parseInt(varastossa)) {
            console.log("Varastossa ei ole tarpeeksi kyseistä tuotetta.");
        } else {
            lista = {
                "id": id,
                "nimi": nimi,
                "hinta": hinta,
                "maara": maara,
                "myynti": myynti,
                "veroton": veroton,
                "vero": vero,
                "varastossa": varastossa,
                "kuva": kuva
            }
            if (tuote_lista == null) {
                tuote_lista = [lista];
            } else {
                tuote_lista.push(lista);
            }
            localStorage.setItem('tuote_lista', JSON.stringify(tuote_lista));
        }
    }
}

function ostoskori() {
    let tuote_lista = JSON.parse(localStorage.getItem('tuote_lista'));
    if (tuote_lista != null) {
        document.getElementById("osto").innerHTML = "";
        tuote_lista.forEach(nayta_tuote);
        document.getElementById("osto").innerHTML +=  
        `<button type="button" class='ostkbtn' onclick='tyhj()'>Tyhjennä</button>
        <button type="button" class='ostkbtn' onclick='kassa()'>Kassalle</button>`;
    } else {
        document.getElementById("osto").innerHTML = 
        `<p>Ostoskori on tyhjä.</p>`;
    }
}

function nayta_tuote(tuote) {
    if (tuote.myynti === "kg") {
        var loppu_hinta = (tuote.hinta * Math.floor(tuote.maara) / 1000).toFixed(2);
        var maara = tuote.maara / 1000;
    } else {
        var loppu_hinta = (tuote.hinta * Math.floor(tuote.maara)).toFixed(2);
        var maara = tuote.maara;
    }
    document.getElementById("osto").innerHTML +=
    `<div class='kori'>
        <div>
            <img src=${tuote.kuva}>
        </div>
        <div>
            <span class="close" onclick='poista("${tuote.id}")'>&times;</span>
            <p> Nimi: ${tuote.nimi}</p>
            <p> Hinta: ${tuote.hinta}€/${tuote.myynti}</p>
            <p> Määrä: ${maara} ${tuote.myynti}</p>
            <p> Loppuhinta: ${loppu_hinta}€</p>
        </div
    </div>`;
}

function tyhj() {
    localStorage.clear();
    ostoskori();
}

function poista(id) {
    const lista = JSON.parse(localStorage.getItem("tuote_lista")) || [];
    const uusiLista = lista.filter(item => item.id !== id);

    if (uusiLista.length == 0) {
        tyhj();
    } else {
        localStorage.setItem("tuote_lista", JSON.stringify(uusiLista));
        ostoskori();
    }
}

function fetch_data(nimi, tuote_lista) {
    return fetch("Index.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            [nimi]: JSON.stringify([tuote_lista.id, tuote_lista.maara])
        })
    })
    .then(r => r.json())
}

async function kassa() {
    var hinta_osto = 0;
    modal.style.display = "block";
    document.getElementById("modal").innerHTML = `
        <div id="modal-content" class="modal-content">
            <div id="modal-error"></div>
            <span class="close" onclick='closeModal()'>&times;</span>
            <table id="modal-table">
                <tr>
                    <th>Tuote</th>
                    <th>Hinta</td>
                    <th>Määrä</th>
                    <th>Loppuhinta</th>
                </tr>
        </div>
    `;
    let tuote_lista = JSON.parse(localStorage.getItem('tuote_lista'));

    for (const tuote of tuote_lista) {
        const data = await fetch_data("kassa", tuote);
        if (data.varastossa === false) {
            document.getElementById("modal-error").innerHTML +=
            `Tuotetta: "${data.nimi}" ei ollut tarpeeksi varatossa`;
            console.log(data.varastossa);
        } else {
            if (data.myynti === "kg") {
                var maara = data.maara / 1000;
            } else {
                var maara = data.maara;
            }
            document.getElementById("modal-table").innerHTML += `
                <tr>
                    <td>${data.nimi}</td>
                    <td>${data.hinta}€/${data.myynti}</td>
                    <td>${maara} ${data.myynti}</td>
                    <td style="text-align: right;">${data.loppu_hinta}€</td>
                </tr> 
            `;
            hinta_osto = (Number(hinta_osto) + data.loppu_hinta).toFixed(2);
        }
        
    }

    document.getElementById("modal-table").innerHTML += `
    <tr>
        <td colspan="3">Yhteensä:</td>
        <td style="text-align: right;">${hinta_osto}€</td>
    </tr>`;
    document.getElementById("modal-content").innerHTML += `
    </table>
    <button type="button" class='ostkbtn' onclick='maksa()'>Maksa</button>`;
}

function maksa() {
    let tuote_lista = JSON.parse(localStorage.getItem('tuote_lista'));
    for (const tuote of tuote_lista) {
        fetch_data("osto", tuote);
    }

    closeModal();
    kuitti();
    tyhj();
}

function kuitti() {
    var loppu_summa = 0;
    let tuote_lista = JSON.parse(localStorage.getItem('tuote_lista'));
    var alv = [];
    var maara = [];
    var veroton = [];
    var verollinen = [];

    const d = new Date();
    
    modal.style.display = "block";
    document.getElementById("modal").innerHTML = `
        <div id="modal-content" class="modal-content">
            <span class="close" onclick='closeModal()'>&times;</span>
            <table id="kuitti">
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>${d.getHours()}:${d.getMinutes()}</th>
                    <th>${d.getDate()}-${d.getMonth() + 1}-${d.getFullYear()}</th>
                </tr>
            </table>
        </div>
    `;

    for (tuote of tuote_lista) {
        if (tuote.myynti === "kg") {
            var loppu_hinta = (tuote.hinta * Math.floor(tuote.maara) / 1000).toFixed(2);
            var veroton_hinta = (tuote.veroton * Math.floor(tuote.maara) / 1000).toFixed(2);
            var tuote_maara = tuote.maara / 1000;
        } else {
            var loppu_hinta = (tuote.hinta * Math.floor(tuote.maara)).toFixed(2);
            var veroton_hinta = (tuote.veroton * Math.floor(tuote.maara)).toFixed(2);
            var tuote_maara = tuote.maara;
        }
        document.getElementById("kuitti").innerHTML += `
            <tr>
                <td colspan="4">${tuote.nimi} - ${tuote_maara} ${tuote.myynti} - ${tuote.hinta} €/${tuote.myynti}</td>
                <td style="text-align: right;">${loppu_hinta}</td>
            </tr>
        `;

        loppu_summa = (Number(loppu_summa) + Number(loppu_hinta)).toFixed(2);

        if (alv.indexOf(tuote.vero) === -1) {
            alv.push(tuote.vero);
            maara.push(1);
            veroton.push(veroton_hinta);
            verollinen.push(loppu_hinta);
        } else {
            var index = alv.indexOf(tuote.vero);
            maara[index] = Number(maara[index]) + 1;
            veroton[index] = (Number(veroton[index]) + Number(veroton_hinta)).toFixed(2);
            verollinen[index] = (Number(verollinen[index]) + Number(loppu_hinta)).toFixed(2);
        }
    }

    document.getElementById("kuitti").innerHTML += `
        <tr>
            <td colspan="4">Yhteensä</td>
            <td style="text-align: right;">${loppu_summa}</td>
        </tr>
        <tr>
            <td colspan="2">ALV</td>
            <td>Veroton</td>
            <td>Vero</td>
            <td>Verollinen</td>
        </tr>
    `;

    for (var kohta = 0; kohta < alv.length; kohta++) {
        document.getElementById("kuitti").innerHTML += `
            <tr>
                <td colspan="2">${maara[kohta]} - ${alv[kohta]}%</td>
                <td>${veroton[kohta]}</td>
                <td>${(verollinen[kohta] - veroton[kohta]).toFixed(2)}</td>
                <td style="text-align: right;">${verollinen[kohta]}</td>
            </tr>
        `;

        if (kohta != 0) {
            veroton[0] = (Number(veroton[0]) + Number(veroton[kohta])).toFixed(2);
            verollinen[0] = (Number(verollinen[0]) + Number(verollinen[kohta])).toFixed(2);
        }
    }

    document.getElementById("kuitti").innerHTML += `
        <tr>
            <td colspan="2">Yhteensä</td>
            <td>${veroton[0]}</td>
            <td>${(verollinen[0] - veroton[0]).toFixed(2)}</td>
            <td style="text-align: right;">${verollinen[0]}</td>
        </tr>
    `;
}