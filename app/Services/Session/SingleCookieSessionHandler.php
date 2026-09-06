<?php

namespace App\Services\Session;

use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Support\InteractsWithTime;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class SingleCookieSessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    protected CookieJar $cookie;
    protected ?Request $request = null;
    protected int $minutes;
    protected bool $expireOnClose;
    protected string $cookieName;

    public function __construct(CookieJar $cookie, int $minutes, bool $expireOnClose = false, string $cookieName = 'zacma_session_data')
    {
        $this->cookie = $cookie;
        $this->minutes = $minutes;
        $this->expireOnClose = $expireOnClose;
        $this->cookieName = $cookieName;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string|false
    {
        $value = '';
        if ($this->request && $this->request->cookies->has($this->cookieName)) {
            $value = $this->request->cookies->get($this->cookieName) ?: '';
        } elseif (isset($_COOKIE[$this->cookieName])) {
            $value = $_COOKIE[$this->cookieName];
        }

        if (!empty($value) && !is_null($decoded = json_decode($value, true)) && is_array($decoded) &&
            isset($decoded['expires']) && $this->currentTime() <= $decoded['expires']) {
            return $decoded['data'] ?? '';
        }

        return '';
    }

    public function write($sessionId, $data): bool
    {
        $this->cookie->queue($this->cookieName, json_encode([
            'data' => $data,
            'expires' => $this->availableAt($this->minutes * 60),
        ]), $this->expireOnClose ? 0 : $this->minutes);

        return true;
    }

    public function destroy($sessionId): bool
    {
        $this->cookie->queue($this->cookie->forget($this->cookieName));
        return true;
    }

    public function gc($maxlifetime): int
    {
        return 0;
    }
}
