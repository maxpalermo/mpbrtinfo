{**
 * Module BRT Info
 *
 * @author    MP Software di Massimiliano Palermo
 * @copyright 2024 MP Software
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-envelope"></i>
        <span>{l s='Anteprima Email' mod='mpbrtinfo'}</span>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{l s='Template Email' mod='mpbrtinfo'}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="list-group">
                            {foreach $emails as $email}
                                <a href="#" class="list-group-item email-template" data-template="{$email|escape:'htmlall':'UTF-8'}">
                                    {$email|escape:'htmlall':'UTF-8'}
                                </a>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title email-title">{l s='Seleziona un template' mod='mpbrtinfo'}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="email-editor-container">
                                        <textarea id="email-editor" class="form-control" rows="20"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="d-flex justify-content-start">
                                    <button id="save-email-template" class="btn btn-success" disabled>
                                        <div class="material-icons mr-1">save</div>
                                        <div>{l s='Salva Template' mod='mpbrtinfo'}</div>
                                    </button>
                                    <div class="ml-1 mr-1" style="border-left: 1px solid #ddd"></div>
                                    <button id="preview-email-template" class="btn btn-info" disabled>
                                        <div class="material-icons mr-1">preview</div>
                                        <div>{l s='Anteprima' mod='mpbrtinfo'}</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel" id="email-preview-panel" style="display: none;">
                    <div class="panel-heading">
                        <h3 class="panel-title">{l s='Anteprima Email' mod='mpbrtinfo'}</h3>
                    </div>
                    <div class="panel-body">
                        <div id="email-preview-container" style="border: 1px solid #ddd; padding: 15px; background-color: #fff;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="emailPreviewModalLabel">{l s='Anteprima Email' mod='mpbrtinfo'}</h4>
            </div>
            <div class="modal-body">
                <iframe id="email-preview-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Chiudi' mod='mpbrtinfo'}</button>
            </div>
        </div>
    </div>
</div>

<script>
    {literal}
        document.addEventListener('DOMContentLoaded', function() {
            // Inizializza CKEditor
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('email-editor', {
                    height: 400,
                    entities: false,
                    allowedContent: true,
                    fullPage: true
                });
            }

            // Gestione click sui template email
            document.querySelectorAll('.email-template').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const template = this.getAttribute('data-template');
                    loadEmailTemplate(template);

                    // Evidenzia il template selezionato
                    document.querySelectorAll('.email-template').forEach(el => el.classList.remove('active'));
                    this.classList.add('active');

                    // Abilita i pulsanti
                    document.getElementById('save-email-template').disabled = false;
                    document.getElementById('preview-email-template').disabled = false;

                    // Aggiorna il titolo
                    document.querySelector('.email-title').textContent = template;
                });
            });

            // Carica il contenuto del template email
            function loadEmailTemplate(template) {
                fetch(adminControllerURL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ajax: 1,
                            action: 'getEmailTemplate',
                            template: template
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.content) {
                            if (typeof CKEDITOR !== 'undefined') {
                                CKEDITOR.instances['email-editor'].setData(data.content);
                            } else {
                                document.getElementById('email-editor').value = data.content;
                            }
                        } else {
                            showError(data.message || 'Errore nel caricamento del template');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Errore nella richiesta: ' + error.message);
                    });
            }

            // Salva il template email
            document.getElementById('save-email-template').addEventListener('click', function() {
                const template = document.querySelector('.email-template.active').getAttribute('data-template');
                const content = typeof CKEDITOR !== 'undefined' ?
                    CKEDITOR.instances['email-editor'].getData() :
                    document.getElementById('email-editor').value;

                fetch(adminControllerURL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ajax: 1,
                            action: 'saveEmailTemplate',
                            template: template,
                            content: content
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message || 'Template salvato con successo');
                        } else {
                            showError(data.message || 'Errore nel salvataggio del template');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Errore nella richiesta: ' + error.message);
                    });
            });

            // Anteprima del template email
            document.getElementById('preview-email-template').addEventListener('click', function() {
                const content = typeof CKEDITOR !== 'undefined' ?
                    CKEDITOR.instances['email-editor'].getData() :
                    document.getElementById('email-editor').value;

                // Mostra l'anteprima in un iframe nel modal
            const iframe = document.getElementById('email-preview-iframe');
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(content);
            iframeDoc.close();

            // Mostra il modal
            $('#emailPreviewModal').modal('show');
        });

        // Funzioni di utilità per mostrare messaggi
        function showSuccess(message) {
            Swal.fire({
                title: 'Successo',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }

        function showError(message) {
            Swal.fire({
                title: 'Errore',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
                });
            }
        });
    {/literal}
</script>