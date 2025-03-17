<script type="module" defer>
    import { fetchShippingOrders } from "{$fetchShippingOrdersPath}";
    import { BrtEsiti } from "{$BrtEsitiPath}";
    import { GetTotalShippings} from "{$GetTotalShippingsPath}";
    import { GetOrderTracking } from "{$GetOrderTrackingPath}";
    import { GetOrderInfo } from "{$GetOrderInfoPath}";

    const baseAdminUrl = "{$baseAdminUrl}";
    const fetchController = "{$fetchController}";
    const translations = {
        error: '{l s="Errore" d="Modules.Mpbrtinfo.Admin"}',
        success: '{l s="Successo" d="Modules.Mpbrtinfo.Admin"}',
        loading: '{l s="Caricamento in corso..." d="Modules.Mpbrtinfo.Admin"}'
    };
    let controller = new AbortController();
    let signal = controller.signal;
    let progressModal = null;
    const updateProgressBar = (progress) => {

    };
    const completeProgress = () => {

    };

    const moduleFetchShippingOrdersInstance = new fetchShippingOrders(fetchController, {
        signal: signal,
        progressModal: progressModal,
        updateProgressBar: updateProgressBar,
        completeProgress: completeProgress
    });
    const moduleBrtEsitiInstance = new BrtEsiti(fetchController, translations);

    window.FetchShippingOrdersInstance = moduleFetchShippingOrdersInstance;
    window.BrtEsitiInstance = moduleBrtEsitiInstance;
    window.progressModal = progressModal;
    window.updateProgressBar = updateProgressBar;
    window.completeProgress = completeProgress;
    window.GetTotalShippingsInstance = new GetTotalShippings(fetchController);
    window.GetOrderTrackingInstance = new GetOrderTracking(fetchController);
    window.GetOrderInfoInstance = new GetOrderInfo(fetchController);

    document.addEventListener("DOMContentLoaded", () => {
        window.dispatchEvent(new Event('modulesReady'));
    });
</script>