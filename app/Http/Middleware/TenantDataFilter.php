<?php

namespace App\Http\Middleware;

use Closure;
use TenantScope;

use App\Tenant;

class TenantDataFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->setTenantByHost($request)) {
            if (!$this->setTenantByReferer($request)) {
                if (!$this->setTenantByParams($request)) {
                    abort(404, 'the tenant does not exist');
                }
            }
        }

        return $next($request);
    }

    /**
     *
     */
    public function setTenantByHost($request) {
        $host = $request->header()['host'][0];
        $Tenant = Tenant::where('domain', $host)->orWhere('sub_domain', $host)->first();

        if ($Tenant) {
            TenantScope::addTenant('tenant_id', $Tenant->id);
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function setTenantByReferer($request) {
        $header = $request->header();

        if (isset($header['referer'])) {
            preg_match('/^http[s]?:\/\/([^\/]*)\//', $header['referer'][0], $tenantDomain);
            $referer = $tenantDomain[1];

            $Tenant = Tenant::where('domain', $referer)->orWhere('sub_domain', $referer)->first();
            if ($Tenant) {
                TenantScope::addTenant('tenant_id', $Tenant->id);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function setTenantByParams($request) {
        $domain = $request->domain;

        if ($domain) {
            preg_match('/^[http[s]?:\/\/]?([^\/]*)[\/]?/', urldecode($domain), $tenantDomain);
            $domain = $tenantDomain[1];
            $Tenant = Tenant::where('domain', $domain)->orWhere('sub_domain', $domain)->first();

            if ($Tenant) {
                TenantScope::addTenant('tenant_id', $Tenant->id);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
