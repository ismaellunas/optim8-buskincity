<?php

namespace Tests\Feature\RolePermission;

use App\Models\Media;
use App\Models\User;
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
            [[
                'file_name' => Str::slug($this->faker->sentence()),
                'file' => UploadedFile::fake()->image($fileName),
                'translations' => [
                    'en' => [
                        'alt' => null,
                        'description' => null,
                    ],
                ],
            ]]
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
    public function deleteDefaultTypeMediaCanBePerformedByTheAuthorWithDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $media = Media::factory()->author($this->user)->create();

        $this->mockMediaService('destroy', $media);

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteDefaultTypeMediaCanBePerformedByNonAuthorWithDeleteOtherUserMediaPermission()
    {
        // Arrange
        $this->givePermissionToRole(['delete', 'other_users']);

        $media = Media::factory()->author(User::factory()->create())->create();

        $this->mockMediaService('destroy', $media);

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteDefaultTypeMediaCannotBePerformedByUserWithoutDeletePermission()
    {
        // Arrange
        $media = Media::factory()->create();
        $usersMedia = Media::factory()->author($this->user)->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$usersMedia->id]));

        // Assert
        $response->assertForbidden();

        // Act
        $this->givePermissionToRole('other_users');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteDefaultTypeMediaCannotBePerformedByNonAuthorWithoutDeleteOtherUsersMediaPermission()
    {
        // Arrange
        $media = Media::factory()
            ->author(User::factory()->create())
            ->create();

        // Act
        $this->givePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$media->id]));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateImageCanBeAccessedByMediaAuthorWithEditPermission()
    {
        // Arrange
        $media = Media::factory()->author($this->user)->create();

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
    public function updateImageCanBeAccessedByNonMediaAuthorWithEditOtherUserMediaPermission()
    {
        // Arrange
        $media = Media::factory()->author(User::factory()->create())->create();

        $this->mockMediaService('replace', $media);

        // Act
        $this->givePermissionToRole(['edit', 'other_users']);

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
    public function updateImageCannotBeAccessedByUserWithoutMediaEditPermission()
    {
        // Arrange
        $media = Media::factory()->author(User::factory()->create())->create();
        $usersMedia = Media::factory()->author($this->user)->create();

        // Act
        $this->revokePermissionToRole('edit');
        $this->givePermissionToRole('other_users');

        $response = $this->post(
            route($this->baseRouteName.'.update-image', ['medium' => $usersMedia->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertForbidden();

        // Act
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
    public function updateImageCannotBeAccessedByNonMediaAuthorWithoutEditOtherUsersMediaPermission()
    {
        // Arrange
        $media = Media::factory()->author(User::factory()->create())->create();

        // Act
        $this->givePermissionToRole('edit');
        $this->revokePermissionToRole('other_users');

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
    public function saveAsMediaCanBeAccessedByAuthorWithEditPermission()
    {
        // Arrange
        $media = Media::factory()->author($this->user)->create();

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
    public function saveAsMediaCanBeAccessedByNonAuthorWithEditOtherUsersMediaPermission()
    {
        // Arrange
        $media = Media::factory()->author(User::factory()->create())->create();

        $this->mockMediaService('duplicateImage', $media);

        // Act
        $this->givePermissionToRole(['edit', 'other_users']);

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
    public function saveAsMediaCannotBeAccessedByUserWithoutEditPermission()
    {
        // Arrange
        $media = Media::factory(User::factory()->create())->create();
        $usersMedia = Media::factory($this->user)->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->post(
            route($this->baseRouteName.'.save-as-image', ['medium' => $usersMedia->id]),
            ['image' => UploadedFile::fake()->image('photo1.jpg')]
        );

        // Assert
        $response->assertForbidden();

        // Act
        $this->givePermissionToRole('other_users');

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
    public function saveAsMediaCannotBeAccessedByNonAuthorWithoutEditOtherUsersMediaPermission()
    {
        // Arrange
        $media = Media::factory(User::factory()->create())->create();

        // Act
        $this->givePermissionToRole('edit');
        $this->revokePermissionToRole('other_users');

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
