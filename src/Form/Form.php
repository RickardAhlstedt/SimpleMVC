<?php

namespace SimpleMVC\Form;

class Form
{
    private array $fields = [];
    private array $errors = [];
    private array $data = [];

    public function addField(string $name, string $type = 'text', array $options = []): self
    {
        $this->fields[$name] = [
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function handleRequest(array $request): self
    {
        $this->data = [];
        foreach ($this->fields as $name => $field) {
            $this->data[$name] = $request[$name] ?? null;
        }
        return $this;
    }

    public function validate(): bool
    {
        $this->errors = [];
        foreach ($this->fields as $name => $field) {
            $value = $this->data[$name] ?? null;
            if (($field['options']['required'] ?? false) && empty($value)) {
                $this->errors[$name] = 'This field is required.';
            }
            if (isset($field['options']['minLength']) && strlen($value) < $field['options']['minLength']) {
                $this->errors[$name] = 'Minimum length is ' . $field['options']['minLength'];
            }
            // Add more validation rules as needed
        }
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function render(): string
    {
        $html = '<form method="post">';
        foreach ($this->fields as $name => $field) {
            $type = $field['type'];
            $value = htmlspecialchars($this->data[$name] ?? '');
            $html .= "<label for=\"$name\">$name</label>";
            $html .= "<input type=\"$type\" name=\"$name\" id=\"$name\" value=\"$value\">";
            if (isset($this->errors[$name])) {
                $html .= "<span style=\"color:red\">{$this->errors[$name]}</span>";
            }
            $html .= "<br>";
        }
        $html .= '<button type="submit">Submit</button></form>';
        return $html;
    }
}