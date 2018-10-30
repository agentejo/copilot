<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-flex-item-1">Copilot</strong>
            <span class="uk-badge {{ (strtolower($license)=='trial') ? 'uk-badge-danger':'' }} uk-flex uk-flex-middle uk-text-uppercase"><span>{{ $license }}</span></span>
            @if(strtolower($license)=='trial')
            <a class="uk-button uk-button-link uk-button-small uk-margin-small-left" href="http://agentejo.com" target="_blank">@lang('Buy a license')</a>
            @endif
        </div>

        <div class="uk-margin">

            @if($home || $pages->count())
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
            @else
                @lang('No pages')
            @endif

        </div>

        <div class="uk-panel-box-footer">

            <ul class="uk-grid uk-grid-small uk-text-small ">
                <li class="uk-flex-item-1 uk-text-truncate"><a href="{{ $app->pathToUrl('site:') }}" target="_blank"><i class="uk-icon-globe"></i> @lang('View page')</a></li>
                <li><a class="uk-link-muted" href="@route('/copilot')"><i class="uk-icon-sitemap"></i> @lang('Pages')</a></li>
                <li><a class="uk-link-muted" href="@route('/copilot/settings')"><i class="uk-icon-cog"></i> @lang('Settings')</a></li>
                <li><a class="uk-link-muted" href="@route('/copilot/finder')"><i class="uk-icon-folder-o"></i> @lang('Files')</a></li>
            </ul>

        </div>

    </div>

</div>
