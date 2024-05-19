<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-cogs"></i>
        <span>Legenda Icone</span>
    </div>
    <div class="panel-body">
        <div class="row text-center">
            <div class="col mr-2">
                {foreach $icons as $key=>$icon}
                    <span class="btn btn-default">
                        <img src="{$icon}" style="width:32px; height:32px; object-fit:contain;">
                        <span>{$key}</span>
                    </span>
                {/foreach}
            </div>
        </div>
    </div>
</div>