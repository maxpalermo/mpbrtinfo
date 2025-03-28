{*

anno_spedizione:"..."
carrier_id:319
carrier_name:"BRT Corriere Espresso"
date_delivered:"..."
date_shipped:"..."
days:"2"
event_color:"#4CAF50"
event_email:"consegnato.html"
event_filiale_id:"159"
event_filiale_name:"..."
event_icon:"check_circle"
event_id:"704"
event_id_order_state:"183"
event_is_delivered:"1"
event_is_shipped:"0"
event_name:"CONSEGNATA"
id_collo:"..."
is_delivered:"1"
is_shipped:"0"
note:""
order_id:...
rma:""
rmn:"..."

carrier_link: "https://vas.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&Nspediz=..."
carrier_image: "https://workwear.maxpalermo.it/img/s/319.jpg"
carrier_name: "BRT Corriere Espresso"
tracking_number: "..."

*}

{if $event == false or $event.isEmpty}
    <div
         class="brt-info-button d-flex justify-content-center align-items-center pointer"
         data-id_order="{$event.id_order}"
         data-id_collo="{$event.tracking_number}"
         data-tippy-content="
            Corriere: {$event.carrier_name}
            {if $event.tracking_number} <br>Tracking: {$event.tracking_number}{/if}
        ">
        <img src="{$event.carrier_image}" style="max-width: 64px; object-fit: contain;">
    </div>
{else}
    <a
       {if isset($event.event_icon) and $event.event_icon and isset($event.carrier_link)}
           href="{$event.carrier_link}"
           target="_blank"
       {else}
           href="javascript:void(0);"
       {/if}
       class="btn brt-info-button"
       data-is_rebind="1"
       data-id_order="{$event.order_id}"
       data-id_collo="{$event.id_collo}"
       data-id_carrier="{$event.carrier_id}"
       data-rmn="{$event.rmn}"
       data-rma="{$event.rma}"
       data-days="{$event.days}"
       data-anno_spedizione="{$event.anno_spedizione}"
       {if isset($event.event_filiale_id) and $event.event_filiale_id}
           data-filiale="({$event.event_filiale_id}) {$event.event_filiale_name}"
       {else}
           data-filiale=""
       {/if}
       data-tippy-content="
            Corriere: {$event.carrier_name}
            {if $event.id_collo} <br>Tracking: {$event.id_collo}{/if}
            {if $event.event_name} <br>Stato attuale: {$event.event_name}{/if}
            ">
        {if !isset($event.event_icon) or !$event.event_icon}
            <img src="{$image}" style="width: 48px; object-fit: contain;">
        {else}
            <div style="width: 48px; height: 48px; border: 4px double {$event.event_color}; display: flex; justify-content: center; align-items: center;">
                <div class="material-icons" style="color: {$event.event_color};">
                    {$event.event_icon}
                </div>
            </div>
        {/if}
    </a>
{/if}