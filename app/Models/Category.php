<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
      'name',
      'slug',
      'parent_id',
      'icon',
      'sort_order',
      'is_active',
    ];

    public function parent()
    {

        return $this->belongsTo(Category::class, 'parent_id');

    }
    public function children()
    {

        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');

    }

    public function childrenRecursive()
    {

        return $this->children()->with('childrenRecursive');

    }
    public function scopeLeaf($query)
    {

        return $query->whereDoesntHave('children');

    }

    public function getFullPathAttribute(): string
    {
        return $this->parent
            ? $this->parent->full_path . ' > ' . $this->name
            : $this->name;
    }
    public function descendantIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->descendantIds());
        }
        return $ids;
    }
    public function ancestors(): array
    {
        $chain = [];
        $node = $this->parent;

        while ($node) {
            array_unshift($chain, $node);
            $node = $node->parent;
        }

        return $chain;
    }
}
