<?php

declare(strict_types=1);

namespace Railgun\Crypto;

use Carbon\Carbon;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use JsonException;
use function json_encode;
use function storage_path;
use const JSON_THROW_ON_ERROR;

class AppleSigninCrypto
{
    private JWK    $jwk;

    public function __construct()
    {
        $this->jwk = JWKFactory::createFromKeyFile(storage_path('keychain/apple-signin.p8'));
    }

    /**
     * Get the client_secret parameter. Apple requires the use of the ES256 algorithm.
     *
     * @see https://developer.apple.com/documentation/sign_in_with_apple/generate_and_validate_tokens
     *
     * @throws JsonException
     */
    public function getSecret(): string
    {
        $alg     = new ES256();
        $payload = json_encode(
            [
                'iss' => 'Q5UCFRCCM5',
                'sub' => 'fr.mathrix.education.service', // given by apple
                'aud' => 'https://appleid.apple.com',
                'exp' => Carbon::now()->addSeconds(30)->timestamp, // 30 seconds TTL
                'iat' => Carbon::now()->timestamp,
            ],
            JSON_THROW_ON_ERROR,
        );

        $builder = new JWSBuilder(new AlgorithmManager([$alg]));
        $jws     = $builder->addSignature($this->jwk, [
            'typ' => 'JWT',
            'alg' => $alg->name(),
            'kid' => '6U36R2W44M', // given by apple
        ])
            ->withPayload($payload)
            ->build();

        return (new CompactSerializer())->serialize($jws);
    }
}
