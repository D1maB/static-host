<?php

namespace App\Console\Commands;

use App\Enums\DomainStatus;
use App\Models\Site;
use Illuminate\Console\Command;

class CheckCustomDomains extends Command
{
    protected $signature = 'sites:check-domains';

    protected $description = 'Check custom domain DNS readiness and update statuses';

    public function handle(): int
    {
        $serverIp = config('app.server_ip');
        $sites = Site::query()->whereNotNull('custom_domain')->get();

        foreach ($sites as $site) {
            $records = @dns_get_record($site->custom_domain, DNS_A) ?: [];
            $addresses = array_values(array_unique(array_filter(array_column($records, 'ip'))));

            $ready = ! empty($addresses) && ($serverIp ? in_array($serverIp, $addresses, true) : true);

            $site->update([
                'domain_status' => $ready ? DomainStatus::Ready : DomainStatus::Pending,
            ]);

            $this->info($site->custom_domain.': '.($ready ? 'ready' : 'pending'));
        }

        return self::SUCCESS;
    }
}
