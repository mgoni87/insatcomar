const app = {
    currentStep: 0,
    isEditing: false,
    pagoIncorrecto: false, 
    originalData: {},      
    touchStartX: 0, // Para control de swipe
    touchEndX: 0,   // Para control de swipe

    stories: [
        {
            id: "personales",
            title: "Validación de datos",
            fields: [
                { id: "nombre", label: "Nombre", value: "Juan Perez", type: "text" },
                { id: "dni", label: "DNI / CUIL / CUIT", value: "38990755", type: "number", reg: /^\d{7,11}$/, err: "7 a 11 números, sin puntos" },
                { id: "tel1", label: "Teléfono 1", value: "1154903344", type: "number", reg: /^\d{10}$/, err: "Debe tener 10 dígitos" },
                { id: "tel2", label: "Teléfono 2", value: "1154903345", type: "number", reg: /^\d{10}$/, err: "Debe tener 10 dígitos" },
                { id: "email", label: "Email", value: "juanperez@gmail.com", type: "email", reg: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, err: "Email inválido" }
            ]
        },
        {
            id: "pago",
            title: "Forma de Pago",
            fields: [
                { id: "pago_tipo", label: "Forma de pago", value: "Débito automático por tarjeta de crédito", type: "text" },
                { id: "pago_marca", label: "Marca", value: "VISA", type: "text" },
                { id: "pago_num", label: "Número", value: "**** **** **** 7894", type: "text" },
                { id: "pago_venc", label: "Vencimiento", value: "12/26", type: "text" }
            ]
        },
        {
            id: "domicilio",
            title: "Datos del Domicilio",
            hasMap: true,
            fields: [
                { id: "dir", label: "Dirección", value: "Av. San pedrito 8", type: "text" },
                { id: "loc", label: "Localidad", value: "CABA", type: "text", readonly: true },
                { id: "prov", label: "Provincia", value: "Buenos Aires", type: "text", readonly: true },
                { id: "lat", label: "Latitud", value: "-34.631687", type: "text", reg: /^-?([1-8]?\d(\.\d+)?|90(\.0+)?)$/, err: "Latitud inválida" },
                { id: "lng", label: "Longitud", value: "-58.469041", type: "text", reg: /^-?((1[0-7]\d(\.\d+)?)|([1-9]?\d(\.\d+)?)|180(\.0+)?)$/, err: "Longitud inválida" }
            ]
        },
        {
            id: "instalacion",
            title: "Instalación Técnica",
            type: "info_options",
            image: "direccion_satelital.jpg",
            text: "La antena debe estar colocada sobre una <strong>pared de material con vista al norte</strong>. La vision hacia al satélite debe estar <strong>liberada de obstrucciones</strong> (arboles / edificaciones / etc.). <br><br><strong>Marcá segun corresponda:</strong>",
            options: [
                "Las paredes <strong>son de material</strong> y la vista al norte <strong>está despejada</strong>",
                "Las paredes <strong>no</strong> son de material o la vista al norte <strong>no</strong> está despejada",
                "No estoy seguro/a"
            ]
        },
        {
            id: "wifi",
            title: "Señal Wi-Fi",
            type: "info_options",
            image: "wifi.png",
            buttonText: "¡Entendido!", 
            text: "La señal de wifi <strong>puede</strong> ser limitada cubriendo el ambiente donde está instalado y llegando a los ambientes contiguos.<br><br><b>Asegurate verlo con el técnico al momento de la instalación.</b>",
            options: [] 
        },
        {
            id: "contratacion",
            title: "Tipo de Contratación",
            type: "info_qa", 
            text: "<b>Importante:</b> En caso de solicitar la baja del servicio dentro de los primeros 6 meses, se generará un cargo por la desconexión de los equipos.",
            options: [
                "Contratación Permanente",
                "Contratación Temporal (menor a 6 meses)"
            ],
            buttonText: "Continuar"
        },
        {
            id: "plan",
            title: "Plan Contratado",
            type: "info_qa",
            text: "El plan contratado es el <b>Priorizado 80</b>. <br><br>Este plan cuenta con una velocidad máxima de bajada de 100Mb/s y una velocidad máxima de subida de 3Mb/s. Este plan está enfocado a clientes que tengan un uso moderado del servicio.<br>¿Qué uso le vas a dar al servicio?",
            options: [
                "Laboral",
                "Entretenimiento",
                "Laboral y Entretenimiento"
            ],
            buttonText: "Siguiente"
        },
        {
            id: "compatibilidad",
            title: "Compatibilidad",
            type: "info_cl",
            text: "<ul><li>El servicio, al ser satelital, tiene una latencia de 600ms, esto provoca que <strong>no sea compatible con juegos en línea en tiempo real</strong> (ej. Fornite, Fifa, Roblox, etc).</li><li>Las cámaras compatibles con el servicio satelital son las que graban de manera local en una tarjeta de memoria o en un dvr.</li><li>Las cámaras que graban remotablente en la nube <strong>no son compatibles con el servicio</strong></li></ul>",
            options: [
                "Entiendo que la latencia es de 600ms",
                "Comprendo que el servicio no es compatible con juegos en línea",
                "Acepto que las cámaras en la nube no funcionarán"
                ],
            buttonText: "¡Entendido!"
        },
        {
            id: "facturacion",
            title: "Facturación",
            type: "info_cl",
            text: "<ul><li>El servicio se factura por adelantado los últimos días del mes y vence los días 8 de cada mes.</li><li>En el primer mes se puede generar mas de una factura dependiendo de la fecha de instalación.</li><li>El servicio comienza a facturarse desde el día en el cual se activa.</li></ul>",
            options: [
                "Comprendo que la facturación es por adelantado",
                "Entiendo que el primer mes puede tener más de una factura",
            ], 
            buttonText: "Finalizar"
        }
    ],

    init() {
        this.stories.forEach(s => {
            if (s.fields) {
                this.originalData[s.id] = s.fields.map(f => ({ id: f.id, value: f.value }));
            }
        });

        // Manejo del botón físico "Atrás" del celular
        history.pushState({ step: 0 }, ""); 
        window.onpopstate = () => {
            if (this.currentStep > 0) {
                this.prev();
                history.pushState({ step: this.currentStep }, "");
            }
        };

        // Manejo de Swipe (deslizar)
        document.addEventListener('touchstart', e => {
            this.touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', e => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        });

        this.renderProgressBar();
        this.renderStory();
    },

    handleSwipe() {
        const threshold = 100; 
        if (this.touchEndX - this.touchStartX > threshold) {
            this.prev(); // Swipe a la derecha vuelve atrás
        }
    },

    renderProgressBar() {
        const container = document.getElementById('progress-bar');
        const totalSegments = this.stories.length; 
        container.innerHTML = '';
        
        for (let i = 0; i < totalSegments; i++) {
            let statusClass = '';
            if (i < this.currentStep) {
                statusClass = 'active'; // Ya pasadas
            } else if (i === this.currentStep) {
                statusClass = 'loading'; // La actual animándose
            }
            
            container.innerHTML += `
                <div class="progress-segment">
                    <div class="progress-fill ${statusClass}"></div>
                </div>`;
        }
    },

    renderStory() {
        this.updateBackButton();
        const story = this.stories[this.currentStep];
        const viewport = document.getElementById('story-viewport');
        let contentHTML = '';

        if (story.type === "info_options") {
            const imageHTML = story.image 
                ? `<img src="${story.image}" style="width: 100%; height: auto; display: block;" alt="Info">` 
                : '';

            const optionsHTML = story.options && story.options.length > 0 
                ? story.options.map((opt, i) => `
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 12px; margin-bottom: 10px; cursor: pointer;">
                        <input type="radio" name="install_opt" value="${i}">
                        <span>${opt}</span>
                    </label>
                `).join('') 
                : '';

        contentHTML = `
            <div class="qa-card" style="max-width: 350px;">
                ${imageHTML} 
                <div class="qa-body" style="padding: 15px; text-align: left;">
                    <p style="font-size: 13px; color: #262626; line-height: 1.4; margin-bottom: 15px;">${story.text}</p>
                    ${optionsHTML}
                </div>
            </div>
            <div class="buttons-container" id="action-buttons"> <button class="btn-primary" onclick="app.next()">
                    ${story.buttonText || 'Continuar'}
                </button>
            </div>
        `;
        } else if (story.type === "info_qa") {
            const optionsHTML = (story.options && story.options.length > 0) 
                ? `<div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                    ${story.options.map((opt, i) => `
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; margin-bottom: 12px; cursor: pointer; text-align: left;">
                            <input type="radio" name="install_opt" value="${i}">
                            <span>${opt}</span>
                        </label>
                    `).join('')}
                </div>` 
                : '';

        contentHTML = `
            <div class="qa-card">
                <div class="qa-header">${story.title}</div>
                <div class="qa-body" style="padding: 20px;">
                    <p style="font-size: 14px; color: #262626; line-height: 1.5; text-align: left; margin: 0;">
                        ${story.text}
                    </p>
                    ${optionsHTML}
                </div>
            </div>
            <div class="buttons-container" id="action-buttons"> <button class="btn-primary" onclick="app.next()">${story.buttonText || 'Continuar'}</button>
            </div>
        `;
        } else if (story.type === "info_cl") {
            // Generamos el HTML del texto superior solo si existe
            const headerTextHTML = story.text 
                ? `<p style="font-size: 14px; color: #262626; line-height: 1.5; text-align: left; margin-bottom: 20px;">${story.text}</p>` 
                : '';

            const checklistHTML = story.options.map((opt, i) => `
                <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; margin-bottom: 15px; cursor: pointer; text-align: left; background: #f9f9f9; padding: 10px; border-radius: 8px;">
                    <input type="checkbox" name="check_opt" value="${i}" style="margin-top: 3px;">
                    <span>${opt}</span>
                </label>
            `).join('');

            contentHTML = `
                <div class="qa-card">
                    <div class="qa-header">${story.title}</div>
                    <div class="qa-body" style="padding: 20px;">
                        ${headerTextHTML}
                        <div style="margin-top: 10px;">
                            ${checklistHTML}
                        </div>
                    </div>
                </div>
                <div class="buttons-container" id="action-buttons"> 
                    <button class="btn-primary" onclick="app.next()">${story.buttonText || 'Continuar'}</button>
                </div>
            `;
        } else {
            let fieldsHTML = story.fields.map(f => `
                <div class="info-item">
                    <span class="label">${f.label}</span>
                    <span class="value" id="disp-${f.id}">${f.value}</span>
                    <div class="error-text" id="err-${f.id}">${f.err || ''}</div>
                </div>
            `).join('');

            let mapHTML = '';
            if (story.hasMap) {
                const lat = story.fields.find(f => f.id === 'lat').value;
                const lng = story.fields.find(f => f.id === 'lng').value;
                const margin = 0.005; 
                mapHTML = `
                    <div class="map-frame">
                        <iframe width="100%" height="100%" frameborder="0" scrolling="no" 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=${parseFloat(lng)-margin},${parseFloat(lat)-margin},${parseFloat(lng)+margin},${parseFloat(lat)+margin}&layer=mapnik&marker=${lat},${lng}">
                        </iframe>
                    </div>`;
            }

            contentHTML = `
                <div class="qa-card">
                    <div class="qa-header" id="card-title">${story.title}</div>
                    <div class="qa-body">${fieldsHTML}</div>
                </div>
                ${mapHTML}
                <div class="buttons-container" id="action-buttons">
                    <button class="btn-primary" onclick="app.next()">La información es correcta</button>
                    <button class="btn-secondary" onclick="app.handleIncorrect()">La información es incorrecta</button>
                </div>
            `;
        }

        viewport.style.opacity = 0; 
        setTimeout(() => {
            viewport.innerHTML = contentHTML;
            viewport.style.opacity = 1; 

            // --- NUEVA LÓGICA DE TIEMPO ---
            const btnContainer = document.getElementById('action-buttons');
            if (btnContainer) {
                // Limpiamos cualquier temporizador previo para evitar saltos
                if (this.btnTimer) clearTimeout(this.btnTimer);
                
                // Ocultamos botones al cargar
                btnContainer.classList.remove('visible');
                
                // Los mostramos tras 5 segundos
                this.btnTimer = setTimeout(() => {
                    btnContainer.classList.add('visible');
                }, 0000); 
            }
        }, 150);
    },

    handleIncorrect() {
        const story = this.stories[this.currentStep];
        if (story.id === "pago") {
            this.pagoIncorrecto = true; 
            this.showToast("El medio de pago no es modificable, contactate con tu asesor de ventas.", 5000);
        } else {
            this.toggleEdit(true);
        }
    },

    toggleEdit(editing) {
        this.isEditing = editing;
        const story = this.stories[this.currentStep];
        const btnContainer = document.getElementById('action-buttons');
        
        if (editing) {
            document.getElementById('card-title').innerText = "Corregir información";
            story.fields.forEach(f => {
                const span = document.getElementById(`disp-${f.id}`);
                if (f.readonly) {
                    span.innerHTML = `<input type="text" class="edit-input" style="background: #f0f0f0; color: #888; cursor: not-allowed;" value="${f.value}" readonly>`;
                } else {
                    span.innerHTML = `<input type="${f.type}" class="edit-input" id="input-${f.id}" value="${f.value}">`;
                }
            });
            btnContainer.innerHTML = `<button class="btn-save" onclick="app.save()">Guardar cambios</button>`;
        } else {
            this.renderStory();
        }
    },

    async save() {
        const story = this.stories[this.currentStep];
        let isValid = true;

        story.fields.forEach(f => {
            if (!f.readonly) {
                const inputEl = document.getElementById(`input-${f.id}`);
                if (inputEl) {
                    const inputVal = inputEl.value;
                    if (f.reg && !f.reg.test(inputVal)) {
                        document.getElementById(`err-${f.id}`).style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById(`err-${f.id}`).style.display = 'none';
                        f.value = inputVal; 
                    }
                }
            }
        });

        if (isValid) {
            if (story.id === "domicilio") {
                await this.fetchGeorefData(story);
            }
            this.showToast("Cambios guardados", 2000);
            this.toggleEdit(false);
        }
    },

    async fetchGeorefData(story) {
        const lat = story.fields.find(f => f.id === 'lat').value;
        const lng = story.fields.find(f => f.id === 'lng').value;
        try {
            const response = await fetch(`https://apis.datos.gob.ar/georef/api/ubicacion?lat=${lat}&lon=${lng}`);
            const data = await response.json();
            if (data.ubicacion) {
                story.fields.find(f => f.id === 'loc').value = data.ubicacion.municipio?.nombre || data.ubicacion.localidad_censal?.nombre || "No encontrada";
                story.fields.find(f => f.id === 'prov').value = data.ubicacion.provincia?.nombre || "No encontrada";
            }
        } catch (error) {
            this.showToast("Error consultando ubicación automática", 3000);
        }
    },

    showToast(msg, duration = 2000) {
        const t = document.getElementById('toast');
        t.innerText = msg;
        t.style.display = 'block';
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => { t.style.display = 'none'; }, duration);
    },

    next() {
        const story = this.stories[this.currentStep];

        // Validación para el nuevo formato Checklist (info_cl)
        if (story.type === "info_cl") {
            const checkboxes = document.querySelectorAll('input[name="check_opt"]');
            const checked = document.querySelectorAll('input[name="check_opt"]:checked');

            if (checked.length < checkboxes.length) {
                this.showToast("Debes marcar todos los puntos para continuar", 3000);
                return;
            }
            // Guardamos que aceptó todo
            this.stories[this.currentStep].userSelection = "Aceptó todos los términos";
        }

        // Validación para formatos con Radio Buttons (se mantiene la que ya tenías)
        const radioButtons = document.getElementsByName("install_opt");
        if (radioButtons.length > 0) {
            const selected = document.querySelector('input[name="install_opt"]:checked');
            if (!selected) {
                this.showToast("Debes seleccionar una opción para continuar", 3000);
                return;
            }
            this.stories[this.currentStep].userSelection = story.options[selected.value];
        }

        // Navegación normal
        if (this.currentStep < this.stories.length - 1) {
            this.currentStep++;
            this.renderProgressBar();
            this.renderStory();
        } else {
            this.enviarAlServidor();
        }
    },

    prev() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.renderProgressBar(); // Refresca y dispara animación
            this.renderStory();
        }
    },

    updateBackButton() {
        const backBtn = document.getElementById('back-button');
        if(backBtn) backBtn.style.display = this.currentStep > 0 ? 'flex' : 'none';
    },

    async enviarAlServidor() {
        this.showToast("Enviando verificación...", 3000);
        
        const dataFinal = {};
        const personales = this.stories.find(s => s.id === "personales");
        dataFinal.dni_asunto = personales.fields.find(f => f.id === "dni").value;
        dataFinal.nombre_asunto = personales.fields.find(f => f.id === "nombre").value;

        this.stories.forEach(s => {
            if (s.id === "personales" || s.id === "domicilio") {
                const originales = this.originalData[s.id];
                const cambios = [];
                s.fields.forEach((f, index) => {
                    if (f.value !== originales[index].value) {
                        cambios.push(`${f.label}: ${f.value}`);
                    }
                });
                dataFinal[s.id] = cambios.length > 0 
                    ? `El cliente modificó los siguientes datos: ${cambios.join(", ")}.` 
                    : "Los datos son correctos";
            } 
            else if (s.id === "pago") {
                dataFinal[s.id] = this.pagoIncorrecto 
                    ? "El cliente seleccionó que los datos son incorrectos." 
                    : "Los datos son correctos";
            } 
            else if (s.userSelection) {
                dataFinal[s.id] = s.userSelection;
            }
        });

        try {
            await fetch('./enviar_mail.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataFinal)
            });
            this.showToast("¡Verificación enviada!", 5000);
        } catch (e) {
            this.showToast("Error al enviar", 4000);
        }
    },
};

window.onload = () => app.init();