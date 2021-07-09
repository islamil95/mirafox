{for $i = 0 to $hiddenCount - 1 }
    {Track\Factory\TrackViewFactory::getInstance()->getView($order->tracks[$i])->render()}
{/for}