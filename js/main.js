document.addEventListener('DOMContentLoaded', function() {
    // Menu toggle para dispositivos móviles
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Dropdown mobile
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
        
        if (window.innerWidth < 768) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
        }
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('nav') && !e.target.closest('.menu-toggle')) {
            if (navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
            }
            
            dropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            });
        }
    });

    // Animación scroll suave para anclas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 90,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Cambio de estilo del header al hacer scroll
    const header = document.querySelector('header');
    
    function toggleHeaderStyle() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
    
    window.addEventListener('scroll', toggleHeaderStyle);
    toggleHeaderStyle();

    // Formulario de newsletter
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            if (email) {
                // Aquí iría la lógica para enviar el email al servidor
                alert('¡Gracias por suscribirte a nuestro boletín informativo!');
                this.reset();
            }
        });
    }

    // Cargar municipios según el departamento seleccionado
    function cargarMunicipios(departamentoSelect, municipioSelect) {
        if (!departamentoSelect || !municipioSelect) {
            return;
        }
        
        departamentoSelect.addEventListener('change', function() {
            const departamentoId = this.value;
            municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';
            municipioSelect.disabled = true;
            
            if (departamentoId) {
                // Realizar petición AJAX para obtener municipios
                fetch(`obtener-municipios.php?id_departamento=${departamentoId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        municipioSelect.innerHTML = '<option value="">Seleccione un municipio...</option>';
                        
                        if (data.error) {
                            console.error('Error del servidor:', data.error);
                            return;
                        }
                        
                        if (data.municipios && data.municipios.length > 0) {
                            data.municipios.forEach(municipio => {
                                const option = document.createElement('option');
                                option.value = municipio.id_municipio;
                                option.textContent = municipio.nombre;
                                municipioSelect.appendChild(option);
                            });
                        } else {
                            municipioSelect.innerHTML = '<option value="">No hay municipios disponibles</option>';
                        }
                        
                        municipioSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error al cargar municipios:', error);
                        municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                        municipioSelect.disabled = false;
                    });
            } else {
                municipioSelect.innerHTML = '<option value="">Primero seleccione un departamento...</option>';
                municipioSelect.disabled = false;
            }
        });
    }

    // Inicializar la carga de municipios en los formularios correspondientes
    // Para formulario de deportista
    const deptoNacimientoDeportista = document.getElementById('departamento_nacimiento');
    const ciudadNacimientoDeportista = document.getElementById('ciudad_nacimiento');
    if (deptoNacimientoDeportista && ciudadNacimientoDeportista) {
        cargarMunicipios(deptoNacimientoDeportista, ciudadNacimientoDeportista);
    }
    
    const deptoResidenciaDeportista = document.getElementById('departamento_residencia');
    const ciudadResidenciaDeportista = document.getElementById('ciudad_residencia');
    if (deptoResidenciaDeportista && ciudadResidenciaDeportista) {
        cargarMunicipios(deptoResidenciaDeportista, ciudadResidenciaDeportista);
    }
    
    // Para formulario de acudiente
    const deptoNacimientoAcudiente = document.getElementById('departamento_nacimiento');
    const ciudadNacimientoAcudiente = document.getElementById('ciudad_nacimiento');
    if (deptoNacimientoAcudiente && ciudadNacimientoAcudiente) {
        cargarMunicipios(deptoNacimientoAcudiente, ciudadNacimientoAcudiente);
    }
    
    const deptoResidenciaAcudiente = document.getElementById('departamento_residencia');
    const ciudadResidenciaAcudiente = document.getElementById('ciudad_residencia');
    if (deptoResidenciaAcudiente && ciudadResidenciaAcudiente) {
        cargarMunicipios(deptoResidenciaAcudiente, ciudadResidenciaAcudiente);
    }

    // Validación de formularios (si existe)
    const registrationForm = document.querySelector('.registration-form');
    if (registrationForm) {
        // Validar número de teléfono (solo números)
        const phoneField = document.getElementById('numero_telefono');
        if (phoneField) {
            phoneField.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Validar peso y estatura (solo números y punto decimal)
        const numericFields = document.querySelectorAll('#peso, #estatura');
        numericFields.forEach(field => {
            if (field) {
                field.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            }
        });
        
        // Mostrar vista previa de la imagen
        const fileInput = document.getElementById('foto');
        if (fileInput) {
            const filePreview = document.querySelector('.file-preview');
            const fileName = document.querySelector('.file-name');
            
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    fileName.textContent = file.name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa" class="preview-image">`;
                    }
                    reader.readAsDataURL(file);
                } else {
                    fileName.textContent = 'Ningún archivo seleccionado';
                    filePreview.innerHTML = '';
                }
            });
        }
    }
});