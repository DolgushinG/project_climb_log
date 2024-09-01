<?php

namespace App\Admin\Controllers;

use App\Models\PostCategories;
use App\Models\Posts;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Model;

class PostsController extends Controller
{
    use HasResourceActions;

    const status = ['PUBLISHED' => 'PUBLISHED', 'DRAFT' => 'DRAFT', 'PENDING' => 'PENDING'];
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Posts);
        $grid->title('title');
        $grid->seo_title('seo_title');
        $grid->column('image', 'Афиша')->image('', 100, 100);
        $grid->slug('slug');
        $grid->meta_description('meta_description');
        $grid->meta_keywords('meta_keywords');
        $grid->column('status', 'Статус')->editable('select', self::status);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Posts::findOrFail($id));
        $show->id('id');
        $show->owner('owner_id');
        $show->title('title');
        $show->seo_title('seo_title');
        $show->excerpt('excerpt');
        $show->body('body');
        $show->image('image');
        $show->slug('slug');
        $show->meta_description('meta_description');
        $show->meta_keywords('meta_keywords');
        $show->status('status');
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Posts);
        $form->hidden('id');
        $form->hidden('owner_id')->default(1);
        $form->text('title', 'Название');
        $form->text('seo_title', 'Сео название');
        $form->text('excerpt', 'excerpt');
        $form->summernote('body');
        $form->image('image', 'image')->move('storage/images/blog/posts');
        $form->text('slug', 'slug');
        $form->text('meta_description', 'meta_description');
        $form->text('meta_keywords', 'meta_keywords');
        $form->select('status', 'status')->options(['PUBLISHED' => 'PUBLISHED', 'DRAFT' => 'DRAFT', 'PENDING' => 'PENDING']);
        return $form;
    }
}
