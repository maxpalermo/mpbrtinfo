<div class="form-group">
    <div class="d-flex justify-content-start mb-2">
        <button class="mr-2 btn btn-default align-items-center select-all-states" type="button">
            <div class="material-icons">check_box</div>
            <span>{l s='Seleziona tutti' mod='mpbrtinfo'}</span>
        </button>
        <button class="btn btn-default align-items-center deselect-all-states" type="button">
            <div class="material-icons">check_box_outline_blank</div>
            <span>{l s='Deseleziona tutti' mod='mpbrtinfo'}</span>
        </button>
    </div>
    <div class="grid grid-cols-2 mb-2">
        {foreach $order_states as $state}
            <div type="button" style="border: 4px solid {$state.color}" class="mr-1 mb-1 btn btn-default btn-order-state-skip {if in_array($state.id_order_state, $skip_states)}active{/if}" data-state-id="{$state.id_order_state}">
                <div>
                    <span>{$state.name}</span>
                </div>
            </div>
        {/foreach}
    </div>
    <input type="hidden" id="skip_states" name="{$input_skip_name}" value="">
    <script>
        function updateSkipStates() {
            const buttons = document.querySelectorAll('.btn-order-state-skip');
            const skipStates = [];
            buttons.forEach(button => {
                if (button.classList.contains('active')) {
                    skipStates.push(button.dataset.stateId);
                }
            });
            document.getElementById('skip_states').value = JSON.stringify(skipStates);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateSkipStates();

            document.querySelectorAll('.btn-order-state-skip').forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('active');
                    updateSkipStates();
                });
            });

            document.querySelectorAll('.select-all-states').forEach(button => {
                button.addEventListener('click', function() {
                    const buttons = document.querySelectorAll('.btn-order-state-skip');

                    buttons.forEach(button => {
                        button.classList.add('active');
                    });
                    updateSkipStates();
                });
            });

            document.querySelectorAll('.deselect-all-states').forEach(button => {
                button.addEventListener('click', function() {
                    const buttons = document.querySelectorAll('.btn-order-state-skip');
                    buttons.forEach(button => {
                        button.classList.remove('active');
                    });
                    updateSkipStates();
                });
            });
        });
    </script>
</div>