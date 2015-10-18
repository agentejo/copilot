<div class="uk-grid-margin" data-widget="pages">

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header">
            <strong>@lang('Pages')</strong>
        </div>

        <div class="uk-margin">

            <ul class="uk-list">

                @if($home)
                <li>
                    <a class="uk-display-block" href="@route('/coco/page'.$home->relpath())"><i class="uk-icon-justify uk-icon-home"></i> {{ $home->meta('title') }}</a>
                </li>
                @endif

                @foreach($pages as $page)
                <li>
                    <a class="uk-display-block uk-margin-small-top uk-margin-small-left" href="@route('/coco/page'.$page->relpath())"><i class="uk-icon-justify uk-icon-file-text-o"></i> {{ $page->meta('title') }}</a>
                </li>
                @endforeach
            </ul>

        </div>

        <div class="uk-panel-box-footer uk-bg-light">
            <a href="@route('/coco')">@lang('Manage Pages')</a>
        </div>

    </div>

</div>
