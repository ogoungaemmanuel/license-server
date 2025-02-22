<?php

namespace Xslain\LicenseServer\Http\Controllers\Api;

use Illuminate\Http\Request;

use Xslain\LicenseServer\Models\IpAddress;
use Xslain\UltimateSupport\Supports\IpSupport;
use Xslain\LicenseServer\Services\LicenseService;
use Xslain\LicenseServer\Http\Controllers\BaseController;

class AuthController extends BaseController
{
    /**
     * Login with sanctum
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|uuid',
        ]);

        $domain = $request->input('ls_domain');
        $licenseKey = $request->input('license_key');

        $license = LicenseService::getLicenseByDomain($domain, $licenseKey);

        if ($license) {
            $license->tokens()->where('name', $domain)->delete();

            $ipAddress = IpAddress::where('license_id', $license->id)->first();
            $serverIpAddress = IpSupport::getIpAddress();

            if (!$ipAddress) {
                $ipAddress = IpAddress::create([
                    'license_id' => $license->id,
                    'ip_address' => $serverIpAddress['ip_address'],
                ]);
            }

            if ($ipAddress && $ipAddress->ip_address == $serverIpAddress['ip_address']) {
                $licenseAccessToken = $license->createToken($domain, ['license-access']);

                return [
                    'status' => true,
                    'message' => 'Successfully logged in.',
                    'access_token' => explode('|', $licenseAccessToken->plainTextToken)[1],
                ];
            }

            return response([
                'status' => false,
                'message' => 'This IP address is not allowed. Please contact the license provider.',
            ], 401);
        }

        return response([
            'status' => false,
            'message' => 'Invalid license key or lincese source.',
        ], 401);
    }
}
