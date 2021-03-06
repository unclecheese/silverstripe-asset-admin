<?php

namespace SilverStripe\AssetAdmin\Tests\GraphQL;

use SilverStripe\AssetAdmin\GraphQL\FileTypeCreator;
use SilverStripe\AssetAdmin\Model\ThumbnailGenerator;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Tests\Storage\AssetStoreTest\TestAssetStore;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class FileTypeCreatorTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
        TestAssetStore::activate('FileTypeCreatorTest');
    }

    public function tearDown()
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testThumbnail()
    {
        $this->logInWithPermission('ADMIN');
        /** @var FileTypeCreator $type */
        $type = Injector::inst()->create(FileTypeCreator::class);

        ThumbnailGenerator::config()->set('thumbnail_links', [
            AssetStore::VISIBILITY_PROTECTED => ThumbnailGenerator::INLINE,
            AssetStore::VISIBILITY_PUBLIC => ThumbnailGenerator::URL,
        ]);

        // Build image
        $image = new Image();
        $image->setFromLocalFile(__DIR__.'/../Forms/fixtures/largeimage.png', 'TestImage.png');
        $image->write();

        // protected image should have inline thumbnail
        $thumbnail = $type->resolveThumbnailField($image, [], [], null);
        $this->assertStringStartsWith('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAWAAAADr', $thumbnail);

        // public image should have url
        $image->publishSingle();
        $thumbnail = $type->resolveThumbnailField($image, [], [], null);
        $this->assertEquals('/assets/FileTypeCreatorTest/8cf6c65fa7/TestImage__FitWzM1MiwyNjRd.png', $thumbnail);

        // Public assets can be set to inline
        ThumbnailGenerator::config()->merge('thumbnail_links', [
            AssetStore::VISIBILITY_PUBLIC => ThumbnailGenerator::INLINE,
        ]);
        $thumbnail = $type->resolveThumbnailField($image, [], [], null);
        $this->assertStringStartsWith('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAWAAAADr', $thumbnail);

        // Protected assets can be set to url
        // This uses protected asset adapter, so not direct asset link
        ThumbnailGenerator::config()->merge('thumbnail_links', [
            AssetStore::VISIBILITY_PROTECTED => ThumbnailGenerator::URL,
        ]);
        $image->doUnpublish();
        $thumbnail = $type->resolveThumbnailField($image, [], [], null);
        $this->assertEquals('/assets/8cf6c65fa7/TestImage__FitWzM1MiwyNjRd.png', $thumbnail);
    }
}
