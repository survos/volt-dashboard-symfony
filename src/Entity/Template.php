<?php

namespace App\Entity;

class Template
{
    public ?string $extends=null;
    public string $body = '';
    public array $blocks = [];
    public string $fn = '';

    public string $bodyBlock = 'body';

    public function addBlock($title, $content)
    {
        $this->blocks[$title] = $content;
    }

    public function toTwig()
    {
        if (empty($this->extends)) {
            return $this->body;
        }
        // use Twig to render twig??  Better to use SurvosMaker and the .tpl files
        $twig = <<< END
{% extends "$this->extends" %}

{% block $this->bodyBlock %}
$this->body
{% endblock %}
END;

        foreach ($this->blocks as $title=>$content) {
            $twig .= "\n\n{% block $title %}\n" . $content . "\n{% endblock %}\n";
        }

        return $twig;

    }

    /**
     * @return string
     */
    public function getExtends(): string
    {
        return $this->extends;
    }

    /**
     * @param string $extends
     * @return Template
     */
    public function setExtends(?string $extends): Template
    {
        $this->extends = $extends;
        return $this;
    }

}
