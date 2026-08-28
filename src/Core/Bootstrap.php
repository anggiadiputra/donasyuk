<?php
namespace DonasiYuk\Core;

use DonasiYuk\Domain\MobileSdk\MobileSdkService;
use DonasiYuk\Domain\MobileSdk\MobileSdkServiceInterface;
use DonasiYuk\Domain\Webhook\WebhookDispatcher;
use DonasiYuk\Domain\Webhook\WebhookDispatcherInterface;
use DonasiYuk\Domain\Audit\AuditLogService;
use DonasiYuk\Domain\Audit\AuditLogServiceInterface;
use DonasiYuk\Domain\I18n\I18nService;
use DonasiYuk\Domain\I18n\I18nServiceInterface;
use DonasiYuk\Domain\Receipt\ReceiptService;
use DonasiYuk\Domain\Receipt\ReceiptServiceInterface;
use DonasiYuk\Domain\Recurring\RecurringDonationService;
use DonasiYuk\Domain\Recurring\RecurringDonationServiceInterface;
use DonasiYuk\Domain\Calculator\CalculatorService;
use DonasiYuk\Domain\Calculator\CalculatorServiceInterface;
use DonasiYuk\Domain\Dashboard\RealtimeDashboardService;
use DonasiYuk\Domain\Dashboard\RealtimeDashboardInterface;
use DonasiYuk\Domain\Fundraising\FundraiserService;
use DonasiYuk\Domain\Fundraising\FundraiserServiceInterface;
use DonasiYuk\Adapters\Payment\MidtransAdapter;
use DonasiYuk\Adapters\Payment\XenditAdapter;
use DonasiYuk\Adapters\Payment\TripayAdapter;
use DonasiYuk\Adapters\Payment\IpaymuAdapter;
use DonasiYuk\Adapters\Payment\FlipAdapter;
use DonasiYuk\Adapters\Payment\StripeAdapter;
use DonasiYuk\Adapters\WhatsApp\CloudAdapter;
use DonasiYuk\Adapters\WhatsApp\WanotifAdapter;
use DonasiYuk\Adapters\Repository\WpCampaignRepository;
use DonasiYuk\Adapters\Repository\WpDonationRepository;
use DonasiYuk\Domain\Campaign\CampaignService;
use DonasiYuk\Domain\Campaign\CampaignServiceInterface;
use DonasiYuk\Domain\Donation\DonationService;
use DonasiYuk\Domain\Donation\DonationServiceInterface;
use DonasiYuk\Domain\Payment\PaymentService;
use DonasiYuk\Domain\Payment\PaymentServiceInterface;
use DonasiYuk\Domain\WhatsApp\WhatsAppService;
use DonasiYuk\Domain\WhatsApp\WhatsAppServiceInterface;

class Bootstrap {
    private static ?self $instance = null;
    private array $services = [];

    private function __construct() {
        $this->registerServices();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerServices(): void {
        // Defensive: never let a missing class take down every WP request.
        if (class_exists(WpCampaignRepository::class)) {
            $campaignRepo = new WpCampaignRepository();
            if (class_exists(CampaignService::class)) {
                $this->services[CampaignServiceInterface::class] = new CampaignService($campaignRepo);
            }
        } else {
            error_log('[DonasiYuk] WpCampaignRepository missing; CampaignService disabled.');
        }

        if (class_exists(WpDonationRepository::class)) {
            $donationRepo = new WpDonationRepository();
            if (class_exists(DonationService::class)) {
                $this->services[DonationServiceInterface::class] = new DonationService($donationRepo);
            }
        } else {
            error_log('[DonasiYuk] WpDonationRepository missing; DonationService disabled.');
        }

        $paymentService = new PaymentService();
        $paymentService->registerGateway(new MidtransAdapter(defined('DYK_MIDTRANS_SERVER_KEY') ? DYK_MIDTRANS_SERVER_KEY : ''));
        $paymentService->registerGateway(new XenditAdapter(
            defined('DYK_XENDIT_SECRET_KEY') ? DYK_XENDIT_SECRET_KEY : '',
            defined('DYK_XENDIT_WEBHOOK_TOKEN') ? DYK_XENDIT_WEBHOOK_TOKEN : ''
        ));
        $paymentService->registerGateway(new TripayAdapter(defined('DYK_TRIPAY_API_KEY') ? DYK_TRIPAY_API_KEY : ''));
        $paymentService->registerGateway(new IpaymuAdapter(defined('DYK_IPAYMU_API_KEY') ? DYK_IPAYMU_API_KEY : ''));
        $paymentService->registerGateway(new FlipAdapter(defined('DYK_FLIP_SECRET_KEY') ? DYK_FLIP_SECRET_KEY : ''));
        $paymentService->registerGateway(new StripeAdapter(defined('DYK_STRIPE_WEBHOOK_SECRET') ? DYK_STRIPE_WEBHOOK_SECRET : ''));
        $this->services[PaymentServiceInterface::class] = $paymentService;

        $waService = new WhatsAppService();
        $waService->registerProvider(new WanotifAdapter());
        $waService->registerProvider(new CloudAdapter());
        $this->services[WhatsAppServiceInterface::class] = $waService;

        $this->services[FundraiserServiceInterface::class] = new FundraiserService();
        $this->services[RealtimeDashboardInterface::class] = new RealtimeDashboardService();
        $this->services[CalculatorServiceInterface::class] = new CalculatorService();
        $this->services[RecurringDonationServiceInterface::class] = new RecurringDonationService();
        $this->services[ReceiptServiceInterface::class] = new ReceiptService();
        $this->services[I18nServiceInterface::class] = new I18nService();
        $this->services[AuditLogServiceInterface::class] = new AuditLogService();
        $this->services[WebhookDispatcherInterface::class] = new WebhookDispatcher();
        $this->services[MobileSdkServiceInterface::class] = new MobileSdkService(
            $this->services[CampaignServiceInterface::class] ?? null,
            $this->services[DonationServiceInterface::class] ?? null
        );
    }

    public function get(string $serviceClass) {
        return $this->services[$serviceClass] ?? null;
    }
}
