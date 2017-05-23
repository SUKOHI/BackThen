<?php

namespace Sukohi\BackThen;

Trait BackThenTrait {

    private $_old_attributes = [];
    private $_new_attributes = [];
    private $_original_attributes = [];
    private $_revision_user_id = -1;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function($model) {

            $model->afterCreate();

        });
        static::updating(function($model) {

            $model->beforeUpdate();

        });
        static::updated(function($model) {

            $model->afterUpdate();

        });
        static::deleting(function($model) {

            $model->beforeDelete();

        });
        static::deleted(function($model) {

            $model->afterDelete();

        });

    }

    public function addRevision($revision_type) {

        $revision_id = $this->getNewRevisionId();
        $unique_id = $this->getNewRevisionUniqueId();

        foreach ($this->_new_attributes as $column => $new_attribute) {

            $old_attribute = array_get($this->_old_attributes, $column, null);

            if($this->isValidRevisionColumn($column) && $old_attribute != $new_attribute) {

                $revision = new \Sukohi\BackThen\Revision();
                $revision->model = get_class();
                $revision->model_id = $this->id;
                $revision->column_name = $column;
                $revision->revision_type = $revision_type;
                $revision->revision_id = $revision_id;
                $revision->unique_id = $unique_id;
                $revision->old_value = $old_attribute;
                $revision->new_value = $new_attribute;

                if($this->_revision_user_id > 0) {

                    $revision->user_id = $this->_revision_user_id;

                }

                $revision->save();

            }

        }

    }

    // Before
    public function beforeUpdate() {

        $this->_old_attributes = self::find($this->id)->toArray();

    }

    public function beforeDelete() {

        $this->_old_attributes = self::find($this->id)->toArray();

    }

    // After
    public function afterCreate() {

        $this->_new_attributes = $this->attributes;
        $this->addRevision('create');

    }

    public function afterUpdate() {

        $this->_new_attributes = $this->attributes;
        $this->addRevision('update');

    }

    public function afterDelete() {

        $this->_new_attributes = [];

        foreach ($this->attributes as $column => $attribute) {

            $this->_new_attributes[$column] = null;

        }

        $this->addRevision('delete');

    }

    // Accessor
    public function getRevisionHistoriesAttribute() {

        return \Sukohi\BackThen\Revision::where('model', get_class())
            ->where('model_id', $this->id)
            ->orderBy('id', 'asc')
            ->get();

    }

    public function getRevisionIdAttribute() {

        $revision = \Sukohi\BackThen\Revision::where('model', get_class())
            ->where('model_id', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if(!is_null($revision)) {

            return $revision->revision_id;

        }

        return null;

    }

    public function getRevisionUniqueIdAttribute() {

        $revision = \Sukohi\BackThen\Revision::where('model', get_class())
            ->where('model_id', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if(!is_null($revision)) {

            return $revision->unique_id;

        }

        return null;

    }

    // Has
    public function hasRevisionId($revision_id) {

        return $this->revision_histories->groupBy('revision_id')->has($revision_id);

    }

    public function hasRevisionUniqueId($unique_id) {

        return $this->revision_histories->groupBy('unique_id')->has($unique_id);

    }

    // Mutator
    public function setRevisionUserIdAttribute($user_id) {

        $this->_revision_user_id = $user_id;

    }

    // Others
    private function isValidRevisionColumn($column) {

        if(in_array($column, ['id', 'created_at', 'updated_at'])) {

            return false;

        } else if(isset($this->revisions)) {

            return in_array($column, $this->revisions);

        } else if(isset($this->ignore_revisions)) {

            return !in_array($column, $this->ignore_revisions);

        }

        return true;

    }

    // Others
    public function changeRevision($unique_id) {

        $revisions = $this->revision_histories->groupBy('unique_id');

        if($revisions->has($unique_id)) {

            $revision = $revisions->get($unique_id);
            $this->restoreRevisionAttribute($revision);

        }

    }

    public function changeRevisionById($revision_id) {

        $revisions = $this->revision_histories->groupBy('revision_id');

        if($revisions->has($revision_id)) {

            $revision = $revisions->get($revision_id);
            $this->restoreRevisionAttribute($revision);

        }

    }

    public function getRevision($unique_id) {

        $revisions = $this->revision_histories->groupBy('unique_id');

        if($revisions->has($unique_id)) {

            return $revisions->get($unique_id);

        }

        return null;

    }

    public function getRevisionById($revision_id) {

        $revisions = $this->revision_histories->groupBy('revision_id');

        if($revisions->has($revision_id)) {

            return $revisions->get($revision_id);

        }

        return null;

    }

    private function getNewRevisionId() {

        return $this->revision_histories->groupBy('revision_id')->count() + 1;

    }

    private function getNewRevisionUniqueId() {

        return md5(config('app.key') .'-'. uniqid(rand(), true));

    }

    private function restoreRevisionAttribute($revision) {

        $this->_original_attributes = [];

        foreach ($revision as $i => $revision_part) {

            $column = $revision_part->column_name;
            $value = $revision_part->new_value;

            if($this->isValidRevisionColumn($column)) {

                $this->_original_attributes[$column] = $this->getAttribute($column);
                $this->setAttribute($column, $value);

            }

        }

    }

    public function clearRevision() {

        foreach ($this->_original_attributes as $column => $value) {

            $this->setAttribute($column, $value);

        }

    }

}