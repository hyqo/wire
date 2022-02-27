<?php

namespace Hyqo\Wire;

function render_template(string $name, array $data = []): string
{
    return Template::compile($name, $data);
}
