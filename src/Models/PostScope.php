<?php

namespace LaraZeus\Sky\Models;

use Illuminate\Support\Facades\DB;

trait PostScope
{
    public function scopeSticky($query)
    {
        $query->where('post_type', 'post')
            ->whereNotNull('sticky_until')
            ->whereDate('sticky_until', '>=', now())
            ->whereDate('published_at', '<=', now());
    }

    public function scopeNotSticky($query)
    {
        $query->where('post_type', 'post')->where(function ($q) {
            return $q->whereDate('sticky_until', '<=', now())->orWhereNull('sticky_until');
        })
        ->whereDate('published_at', '<=', now());
    }

    public function scopePublished($query)
    {
        $query->where('post_type', 'post')
            ->whereIn('status', ['publish', 'private'])
            ->whereDate('published_at', '<=', now());
    }

    public function scopeRelated($query, $post)
    {
        $query->where('post_type', 'post')
            ->withAnyTags($post->tags->pluck('name')->toArray(), 'category');
    }

    public function scopePage($query)
    {
        $query->where('post_type', 'page')
            ->whereIn('status', ['publish', 'private'])
            ->whereDate('published_at', '<=', now());
    }

    public function scopePosts($query)
    {
        $query->where('post_type', 'post')
            ->whereDate('published_at', '<=', now());
    }

    public function scopeForCategory($query, $category)
    {
        if ($category === null) {
            return $query;
        }

        return $query->where(
            function ($query) use ($category) {
                $query->withAnyTags([$category], 'category');

                return $query;
            }
        );
    }

    public function scopeSearch($query, $term)
    {
        if ($term === null) {
            return $query;
        }

        return $query->where(
            function ($query) use ($term) {
                foreach (['title', 'slug', 'content', 'description'] as $attribute) {
                    $query->orWhere(DB::raw("lower({$attribute})"), 'like', "%{$term}%");
                }

                return $query;
            }
        );
    }
}
