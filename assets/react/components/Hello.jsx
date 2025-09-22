import React from 'react';

export default function Hello({ name = 'World' }) {
  return <div>Hello, {name}!</div>;
}