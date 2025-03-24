<div class="form-group">
    <div class="col-lg-12">
        <div class="input-group">
            <input type="text" id="cron-job-url" class="form-control" readonly value="{$cronJobUrl|escape:'html':'UTF-8'}" style="background-color: #f8f8f8; border-color: #ddd;">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="copy-cron-url">
                    <i class="icon-copy"></i> {l s='Copy' mod='mpbrtinfo'}
                </button>
            </span>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        $('#copy-cron-url').click(function() {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($('#cron-job-url').val()).select();
            document.execCommand("copy");
            $temp.remove();

            // Optional: Show a success message
            showSuccessMessage('{l s='URL copied to clipboard' mod='mpbrtinfo'}');
        });
    });
</script>