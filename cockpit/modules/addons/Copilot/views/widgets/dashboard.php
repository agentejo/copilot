<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-flex-item-1">@lang('Pages')</strong>
            <span class="uk-badge {{ (strtolower($license)=='trial') ? 'uk-badge-danger':'' }} uk-flex uk-flex-middle uk-text-uppercase"><span>{{ $license }}</span></span>
        </div>

        <div class="uk-margin">

            <ul class="uk-list">

                @if($home)
                <li class="uk-text-truncate">
                    <a class="uk-display-block" href="@route('/copilot/page'.$home->relpath())"><i class="uk-icon-justify uk-icon-home"></i> {{ $home->meta('title') }}</a>
                </li>
                @endif

                @foreach($pages as $page)
                <li class="uk-text-truncate">
                    <a class="uk-display-block uk-margin-small-top" href="@route('/copilot/page'.$page->relpath())"><i class="uk-icon-justify uk-icon-file-text-o"></i> {{ $page->meta('title') }}</a>
                </li>
                @endforeach
            </ul>

        </div>

        <div class="uk-panel-box-footer uk-bg-light">
            <ul class="uk-grid uk-grid-small uk-text-small">
                <li><a href="@route('/copilot')"><i class="uk-icon-clone"></i> @lang('Pages')</a></li>
                <li><a href="@route('/copilot/settings')"><i class="uk-icon-cog"></i> @lang('Settings')</a></li>
                <li><a href="@route('/copilot/finder')"><i class="uk-icon-folder-o"></i> @lang('Files')</a></li>
            </ul>

        </div>

    </div>

</div>
