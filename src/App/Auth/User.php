<?php

declare(strict_types=1);

namespace App\Auth;

use JsonSerializable;
use Mezzio\Authentication\UserInterface;
use Webmozart\Assert\Assert;

/**
 * User
 *
 * This object represents the user information as returned by the keycloak server. The array it receives is a standard-ish
 * payload. For example, we can rely on the key 'sub' to be the unique identifier of the user that's logged in. There's
 * a whole load of other stuff available in the payload, exactly what that is depends on the server. Effectively, this
 * object represents the body of a User Info Response.
 *
 * @link https://openid.net/specs/openid-connect-basic-1_0.html#UserInfoResponse
 *
 * You'll notice that roles are empty. This is because roles are not included in the User Info Response. In Keycloak,
 * roles can be determined by decoding the AccessToken and inspecting the claims. KeyCloak specifically places roles
 * in realm_access.roles and resource_access.account.roles as list<non-empty-string>.
 *
 * I guess in a real system, you might inspect these during authentication and populate the roles here via a
 * constructor argument.
 *
 * @psalm-type Payload = array{
 *     sub: non-empty-string,
 * }&array<string, mixed>
 */
final readonly class User implements UserInterface, JsonSerializable
{
    /** @param Payload $details */
    private function __construct(private array $details)
    {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        Assert::isMap($data);
        Assert::allScalar($data);
        Assert::keyExists($data, 'sub');
        Assert::stringNotEmpty($data['sub']);

        return new self($data);
    }

    /** @return Payload */
    public function jsonSerialize(): array
    {
        return $this->details;
    }

    /** @return non-empty-string */
    public function getIdentity(): string
    {
        return $this->details['sub'];
    }

    /** @inheritDoc */
    public function getRoles(): iterable
    {
        return [];
    }

    /** @inheritDoc */
    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }

    /** @return Payload */
    public function getDetails(): array
    {
        return $this->details;
    }
}
