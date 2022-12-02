<?php

namespace Tests\Feature\RolePermission;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;

class MediaPermissionTest extends BaseRolePermissionTestCase
{
    protected $basePermissionName = 'media';
    protected $baseRouteName = 'admin.media';

    protected function setUp(): void
    {
        parent::setUp();

        $this->givePermissionToRole('dashboard', 'system');

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
        ]);
    }

    private function mockMediaService(string $methodName, Media $media): void
    {
        $this->instance(
            MediaService::class,
            Mockery::mock(
                MediaService::class,
                function (MockInterface $mock) use ($media, $methodName) {
                    $mock
                        ->shouldReceive($methodName)
                        ->once()
                        ->andReturn($media);
                }
            )
        );
    }

    /**
     * @test
     */
    public function indexCanBeAccessedByUserWithMediaBrowsePermission()
    {
        // Act
        $this->givePermissionToRole('browse');

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexCannotBeAccessedByUserWhoHasNoMediaBrowsePermission()
    {
        // Act
        $this->revokePermissionToRole('browse');

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->put(
            route($this->baseRouteName.'.update', $media->id),
            [
                'file_name' => $media->file_name,
                'translations' => [
                    'en' => [
                        'alt' => $this->faker->sentence(),
                        'description' => $this->faker->sentence(),
                    ],
                ],
            ]
        );

        // Assert
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $media->id), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithMediaAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function createCannotBeAccessedByUserWhoHasNoMediaAddPermission()
    {
        // Act
        $this->revokePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function storeCanBeAccessedByUserWithMediaAddPermission()
    {
        // Arrange
        $fileName = 'photo1.jpg';

        $this->instance(
            MediaService::class,
            Mockery::mock(
                MediaService::class,
                function (MockInterface $mock) use ($fileName) {
                    $mock
                        ->shouldReceive('upload')
                        ->once()
                        ->andReturn(Media::factory()->create());

                    $mock
                        ->shouldReceive('sanitizeFileName')
                        ->once()
                        ->andReturn($fileName);
                }
            )
        );

        // Act
        $this->givePermissionToRole('add');

        $response = $this->post(
            route($this->baseRouteName.'.store'),
            [
                'file_name' => Str::slug($this->faker->sentence()),
                'file' => UploadedFile::fake()->image($fileName),
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoMediaAddPermission()
    {
        // Act
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCanBeDoneByUserWithMediaDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $media = Media::factory()->create();

        $this->mockMediaService('destroy', $media);

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoDeletePermission()
    {
        // Arrange
        $media = Media::factory()->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateImageCanBeAccessedByUserWhoHasMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        $this->mockMediaService('replace', $media);

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->post(
            route($this->baseRouteName.'.update-image', ['medium' => $media->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertSessionHasNoErrors();
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function updateImageCannotBeAccessedByUserWhoHasNoMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->post(
            route($this->baseRouteName.'.update-image', ['medium' => $media->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function saveAsMediaCanBeAccessedByUserWhoHasMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        $this->mockMediaService('duplicateImage', $media);

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->post(
            route($this->baseRouteName.'.save-as-image', ['medium' => $media->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function saveAsMediaCannotBeAccessedByUserWhoHasNoMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->post(
            route($this->baseRouteName.'.save-as-image', ['medium' => $media->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function listMediaCanBeAccessedByUserWithMediaBrowsePermission()
    {
        // Act
        $this->givePermissionToRole('browse');

        $response = $this
            ->withHeaders(['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->get(route($this->baseRouteName.'.lists'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function listMediaCannotBeAccessedByUserWhoHasNoMediaBrowsePermission()
    {
        // Act
        $this->revokePermissionToRole('browse');

        $response = $this
            ->withHeaders(['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->get(route($this->baseRouteName.'.lists'));

        // Assert
        $response->assertForbidden();
    }
}
