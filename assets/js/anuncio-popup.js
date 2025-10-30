// Función para mostrar el popup
function showAnuncioPopup(anuncioId) {
    // Obtener los detalles del anuncio
    fetch(`../actions/get_anuncio_details.php?id=${anuncioId}`)
        .then(response => response.json())
        .then(anuncio => {
            // Construir el contenido del popup
            const popupContent = `
                <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-text">
                                            ${anuncio.nombreUsuario.substr(0, 2).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold text-lg text-text">${anuncio.nombreUsuario}</h3>
                                            ${anuncio.propietarioVerificado ? `
                                                <span title="Usuario con viajes verificados" class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-[#6EE7B7]/20 text-[#6EE7B7] border border-[#6EE7B7]/30">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Viaje verificado</span>
                                                </span>
                                            ` : ''}
                                        </div>
                                        <div class="flex items-center gap-1 text-text/60">
                                            <i class="fas fa-star text-primary text-xs"></i>
                                            <span>4.8</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="closeAnuncioPopup()" class="text-text/60 hover:text-text">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <span class="px-3 py-1.5 text-xs font-medium rounded-full inline-block ${
                                        anuncio.tipo === 'ofrezco' ? 'bg-primary/10 text-text' : 'bg-secondary/10 text-text'
                                    }">
                                        ${anuncio.tipo === 'ofrezco' ? 'Ofrezco' : 'Busco'}
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        <span class="font-medium text-text">
                                            ${anuncio.origen}
                                            <span class="text-primary mx-2">→</span>
                                            ${anuncio.destino}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap gap-4">
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-calendar text-primary/80"></i>
                                            <span class="text-text/70">${anuncio.fechaPublicacion}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-clock text-primary/80"></i>
                                            <span class="text-text/70">${anuncio.horaSalida}</span>
                                        </div>
                                        ${anuncio.horaRegreso ? `
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clock text-primary/80"></i>
                                                <span class="text-text/70">Regreso: ${anuncio.horaRegreso}</span>
                                            </div>
                                        ` : ''}
                                        ${anuncio.plazasDisponibles ? `
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-users text-primary/80"></i>
                                                <span class="text-text/70">${anuncio.plazasDisponibles} plazas disponibles</span>
                                            </div>
                                        ` : ''}
                                    </div>

                                    ${anuncio.descripcion ? `
                                        <div class="bg-background rounded-lg p-4">
                                            <h4 class="font-medium text-text mb-2">Descripción</h4>
                                            <p class="text-text/70">${anuncio.descripcion}</p>
                                        </div>
                                    ` : ''}

                                    <div class="border-t pt-4 mt-4">
                                        <h4 class="font-medium text-text mb-2">Contacto</h4>
                                        <p class="text-text/70">
                                            <i class="far fa-envelope mr-2 text-primary/80"></i>
                                            ${anuncio.correoUsuario}
                                        </p>
                                    </div>
                                    <div class="mt-4 flex gap-3 items-center">
                                        <a href="messages.php?user=${anuncio.propietarioId}" class="px-4 py-2 bg-primary text-text text-sm font-medium rounded-lg hover:bg-hover transition-colors">
                                            <i class="fas fa-comments mr-2"></i>Contactar
                                        </a>
                                        <a href="profile.php?id=${anuncio.propietarioId}" class="px-4 py-2 bg-background text-text text-sm font-medium rounded-lg border border-gray-200 hover:border-primary/20 transition-colors">
                                            <i class="fas fa-user mr-2"></i>Ver perfil
                                        </a>
                                        ${(!anuncio.esPropietario && !anuncio.usuarioYaUnido) ? `
                                            <button id="unirseBtn_${anuncio.idAnuncio}" onclick="joinAnuncio(${anuncio.idAnuncio})" class="px-4 py-2 bg-secondary text-white text-sm font-medium rounded-lg hover:opacity-90 transition-colors">
                                                <i class="fas fa-sign-in-alt mr-2"></i>Unirse
                                            </button>
                                        ` : ''}
                                        ${anuncio.usuarioYaUnido ? `
                                            <span class="text-sm text-text/70">Inscrito · Estado: ${anuncio.usuarioViajeEstado || 'pendiente'}</span>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Agregar el popup al DOM
            document.body.insertAdjacentHTML('beforeend', popupContent);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del anuncio');
        });
}

// Función para cerrar el popup
function closeAnuncioPopup() {
    const popup = document.querySelector('.fixed.inset-0.bg-black');
    if (popup) {
        popup.remove();
    }
}

// Cerrar el popup al hacer clic fuera de él
document.addEventListener('click', function(event) {
    const popup = document.querySelector('.fixed.inset-0.bg-black');
    if (popup && event.target === popup) {
        closeAnuncioPopup();
    }
});

// Unirse a un anuncio: crea un viaje pendiente
function joinAnuncio(idAnuncio) {
    const btn = document.getElementById(`unirseBtn_${idAnuncio}`);
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = 'Procesando...';

    const data = new URLSearchParams();
    data.append('idAnuncio', idAnuncio);

    fetch('../actions/create_viaje.php', {
        method: 'POST',
        body: data
    }).then(res => res.json())
      .then(resp => {
          if (resp.ok) {
              btn.outerHTML = `<span class="text-sm text-text/70">Inscrito · Estado: pendiente</span>`;
          } else if (resp.error) {
              alert(resp.error);
              btn.disabled = false;
              btn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Unirse';
          }
      }).catch(err => {
          console.error(err);
          alert('Error al intentar unirse al viaje');
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Unirse';
      });
}