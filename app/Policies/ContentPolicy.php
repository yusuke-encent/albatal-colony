<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function view(?User $user, Content $content): bool
    {
        if ($content->published_at !== null) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $content->provider_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Content $content): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->isAdmin();
    }
}
