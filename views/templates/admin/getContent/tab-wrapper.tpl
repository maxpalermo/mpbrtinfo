<div class="panel-body">
    <ul class="nav nav-tabs" role="tablist">
        <li class="active">
            <a href="#general" role="tab" data-toggle="tab">{l s='Generale' mod='mpbrtinfo'}</a>
        </li>
        <li>
            <a href="#eventi" role="tab" data-toggle="tab">{l s='Eventi' mod='mpbrtinfo'}</a>
        </li>
        <li>
            <a href="#esiti" role="tab" data-toggle="tab">{l s='Esiti' mod='mpbrtinfo'}</a>
        </li>
        <li>
            <a href="#email" role="tab" data-toggle="tab">{l s='Email' mod='mpbrtinfo'}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="general">
            {$htmlConfigForm}
        </div>
        <div class="tab-pane" id="eventi">
            {$htmlEventi}
        </div>
        <div class="tab-pane" id="esiti">
            {$htmlEsiti}
        </div>
        <div class="tab-pane" id="email">
            {$htmlPreviewMail}
        </div>
    </div>
</div>