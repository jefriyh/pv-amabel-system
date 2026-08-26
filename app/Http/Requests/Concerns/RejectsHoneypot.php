<?php

namespace App\Http\Requests\Concerns;

/**
 * Field umpan yang disembunyikan dengan CSS. Manusia tidak pernah melihatnya,
 * bot pengisi form otomatis biasanya mengisinya — kalau terisi, tolak.
 */
trait RejectsHoneypot
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function honeypotRules(): array
    {
        return ['website' => ['nullable', 'prohibited']];
    }
}
