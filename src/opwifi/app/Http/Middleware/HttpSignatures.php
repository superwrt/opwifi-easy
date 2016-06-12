<?php

namespace App\Http\Middleware;

use Closure, Storage, File;

class HttpSignatures
{
    static public function getPrivKey()
    {
        $keyFile = null;
        if (Storage::disk('local')->has('serv_priv.pem')) {
            $keyFile = Storage::disk('local')->get('op_serv_priv.pem');
        } else if (file_exists(base_path('resources/opwifi/op_serv_priv.pem'))) {
            $keyFile = File::get(base_path('resources/opwifi/op_serv_priv.pem'));
        }
        if ($keyFile) {
            return openssl_pkey_get_private($keyFile);
        }
        return null;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $privKey = self::getPrivKey();

    	if (!$privKey) {
    		return response('No key.', 500);
    	}

        $response = $next($request);

		$sha1 = base64_encode(sha1($response->getContent(), true));
        $response->header('Digest', 'SHA='.$sha1);
        $message = 'Digest: SHA='.$sha1.'\n';
        if (openssl_sign($message, $sign, $privKey, OPENSSL_ALGO_SHA1)) {
     	   $response->header('Signature',
     	   	'keyId="rsa-key1",algorithm="rsa-sha1",headers="digest",signature="'.base64_encode($sign).'"');
    	}
        $response->header('Content-Length', strlen($response->getContent()));

        return $response;
    }
}