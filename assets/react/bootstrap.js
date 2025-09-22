import React from 'react';
import { createRoot } from 'react-dom/client';

const registry = new Map();

export function registerComponent(name, component) {
  registry.set(name, component);
}

export function hydrateReactComponents() {
  const nodes = document.querySelectorAll('[data-react-component]');
  nodes.forEach((node) => {
    const name = node.getAttribute('data-react-component');
    const propsJson = node.getAttribute('data-props') || '{}';
    const props = JSON.parse(propsJson);
    const Cmp = registry.get(name);
    if (!Cmp) return;
    const root = createRoot(node);
    root.render(React.createElement(Cmp, props));
  });
}

document.addEventListener('DOMContentLoaded', hydrateReactComponents);