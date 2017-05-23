# BackThen
A Laravel package for managing reivision of database.
(This package is for Laravel 5+)

* Note: This package is inspired by [VentureCraft/revisionable](https://github.com/VentureCraft/revisionable). Thank you, VentureCraft!

# Installation

Execute the following composer command.

    composer require sukohi/back-then:1.*
    
then set BackThenServiceProvider in your config/app.php.

    Sukohi\BackThen\BackThenServiceProvider::class, 
    
# Preparation

To make a table for this package, execute the following commands.

    php artisan vendor:publish --provider="Sukohi\BackThen\BackThenServiceProvider"

and

    php artisan migrate

Add `BackThenTrait` to your model like so.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use Sukohi\BackThen\BackThenTrait;
    
    class Product extends Model
    {
        use BackThenTrait;

That's all.  
Now when you create, update or delete record, this package automatically will save revision record(s).

# Usage

You can save and delete record as usual like so.
    
    $product = new \App\Product();
    $product->name = 'xxxxxxx';
    $product->save();   // Revision record will be saved.
    
    $product = \App\Product::find(1);
    $product->name = 'yyyyyyy';
    $product->save();   // Revision record will be saved.
    
    $product->delete();   // Revision record will be saved.

# Save user ID

You can save ID of user who saved/deleted through `revision_user_id`.

    $product = \App\Product::find(1);
    $product->revision_user_id = 1; // User ID you want to save.
    $product->save();

# Retrieve Current Revision ID and Unique ID

    echo $product->revision_id;
    echo $product->revision_unique_id;

* You can change revision you want via revision ID or unique ID.

# Change Revision

via `changeRevisionById`

    $revision_id = 1;

    if($product->hasRevisionId($revision_id)) {

        $product->changeRevisionById($revision_id); // This has old values.

    }

or via `changeRevision`  

    $unique_id = '5a906ea1934196bc065f1b22eafd90c9';

    if($product->hasRevisionUniqueId($unique_id)) {

        $product->changeRevision($unique_id); // This has old values.

    }

Note: Once you call `changeRevisionById()` or `changeRevision()`, the model has old values.  
This means if you save it, of cause, the latest value will be replaced with the old ones.  
So don't forget to call `clearRevision()` if you need to save at the same time.

# Return to the Latest Revision

    $product->clearRevision();

# Revision Columns

If you'd like this package to save specific revision record.  
Set column names in your model like so.

    protected $revisions = ['column_name'];
    
or  
    
    protected $ignore_revisions = ['column_name'];

# Retrieve Revision data

    $histories = $product->revisionHistory; // All revisions

or  

    $revision = $product->getRevisionById(1);   // A specific revision
    $revision = $product->getRevision('5a906ea1934196bc065f1b22eafd90c9');// A specific revision

# License

This package is licensed under the MIT License.

Copyright 2017 Sukohi Kuhoh