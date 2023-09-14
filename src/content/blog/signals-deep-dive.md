---
title: 'Deep Dive Into How Signals Work In SolidJS'
pubDate: 'May 08 2023'
original: 'https://www.thisdot.co/blog/deep-dive-into-how-signals-work-in-solidjs/'
heroImage: '/signals-deep-dive.png'
---

SolidJS and Qwik have shown the world the power of signals, and Angular is
following suit. There’s no way around them, so let's see what they are, why one
would use them, and how they work.

## Signal basics

Signals are built using the observer pattern. In this pattern, a subject holds a
list of observers who are subscribed to changes to the subject. Whenever the
subject gets changed, all subscribers will receive a notification of the update.
Typically through a registered callback method. Observers may push new changes
to that subject or other subjects. Triggering another set of updates throughout
the observers.

> From the above, you might have guessed that infinite loops are a big caveat
> with using the observer pattern. The same holds true for signals.

The power of this pattern lies in the separation of concerns. Observers need to
know little about the subject, except that it can change. Whichever actor is
going to change the subject needs to know nothing about the observers. This
makes it easy to build standalone services that know only about their domain.

## Signals in front-end frameworks

SolidJS brings the observer pattern to the table for front-end frameworks
through signals. Observers can be added to a signal through SolidJS’s concept of
Effects (by using `createEffect`).

Components within SolidJS can be seen as both an observer and a subject at the
same time. The component subscribes to all signals that are used to render the
components HTML. SolidJS’s rendering system, in turn, is subscribed to all
components.

Where the components act as subjects and SolidJS as an observer. So whenever a
signal changes in a component, the component reacts by changing its output,
which then triggers SolidJS to put the new output on the screen.

Compare this to, for example, Vue or React. When a change to the state occurs,
the new values are passed down the component hierarchy. Each component returns
its new output, which can be either the same as the previous render or changed.
The framework then compares this tree against what it already had, and
determines which parts to update.

This is more dependent on a single source of truth which needs to know about all
the components in the system. Changes are loosely related to each other, and
only by diffing the results can the next step be determined. This differs from
SolidJS setup, which makes hard connections between changes and results, making
what will get updated when a signal changes more straightforward.

## Writing our own Signals

At first sight, signals seem magical, and one might be inclined to believe there
are some compiler tricks going on. Yet it is all plain JavaScript, and in this
article, we’ll demystify signals in order to use them to their full potential.
We can create our own signals with pure JavaScript in less than 25 lines.

> Our simple version will not take objects or arrays as values as these are
> references in JavaScript and require special attention.

Let's start with the interface. We want the signal creator, which is a function
that returns a tuple with the first value being the getter and the second value
the setter. The function accepts a value, which will be used as the initial
value.

This gives us:

```javascript
function createSignal(initialValue) {
  let value = initialValue;
  const getter = () => value;
  const setter = (newValue) => {
\tvalue = newValue;
  };
  return [getter, setter];
}

const original = 1;
const [count, setCount] = createSignal(original);

console.log('Current count: ', count()); // Expected outcome: “Current count: 1”

setCount(2);

console.log('And now it is', count()); // Expected outcome: “And now it is 2”
console.log('The original is the same', original); // Expected outcome: “The original is the same 1”
```

> Note that, due to the fact that we created a new variable within the closure
> of our `createSignal`, the variable outside of its scope will not change.
> `original` on the last line will still be “1”. For simplicity, we’re going to
> leave objects and arrays out of the picture as these are references instead of
> scalar values, and need extra code to do the same thing.

Now that we can read from and write to our signal (a.k.a. subject), we’ll need
to add subscribers to it. Whenever the getter is called (i.e., the value is
read), we want the originator of the call to be registered as an observer. Then,
when the setter is called, we are going to loop over all subscribed observers,
and notify them of the new value.

Consider this fully working signal creator. We’re almost there.

```javascript
function createSignal(initialValue) {
  let value = initialValue;
  const observers = [];
  const getter = (current) => {
\tif (current && !observers.includes(current)) {
  \tobservers.push(current);
\t}
\treturn value;
  };
  const setter = (newValue) => {
\tvalue = newValue;
\tobservers.forEach((fn) => fn());
  };
  return [getter, setter];
}
```

This snippet has a downside. It needs the observer to be passed as the argument
to the getter. But we don’t want to deal with that. Our interface was to read
`signal()`, and have some sort of magic register the observer for us.

What comes next was an eye-opener for me. I always believed there was some
closure trick, or built-in JavaScript function to retrieve parent closures. That
would have been a fantastic way to get who called the getter function and
register it as an observer. But JavaScript offers nothing to support us in this.
Instead, a way more simple trick is used, and it is seemingly used in every
major framework. Frameworks, among others, React and SolidJS, store the parent
in a global variable.

Because JavaScript is single-threaded, it needs to execute all operations in
order. It does a lot under the hood to get stuff like async to work. Clever
developers have relied on this single-threaded aspect by writing to a global
variable, and reading from it in the next function. This gets a little abstract,
so here’s a concrete example to demonstrate this setup.

```javascript
let current;

function first() {
  console.log(‘we are in first’);
}
function second() {
  current(); // set to function first before calling function second
  console.log(‘we are in second’);
  current = undefined; // clear it out, we’ve used it and don’t want it to pollute 
}
function third() {
  if (current === undefined) {
    console.log(‘there is no current’);
  }
}

current = first;
second();
third();

// Expected output
// we are in first
// we are in second
// there is no current
```

We do not need to worry about `current` getting overwritten, as the code will
always execute in order. It’s safe to assume that it will have the value we
expect it to have when the body of `second` is executed. Note that we clear the
value in the body of `first` as we don’t want unwanted side-effects by leaving
the variable set.

## The Fully Working Signal

Let’s add effects to our signals to complete the minimal signal minimal
framework. With what we have learned in the previous section, we can create
effects by

Registering the effect callback (i.e., the observer) to our global variable
`current` Calling the observer for the first time. This will read all signals it
depends upon, therefore adding `current` to the observer list. Clearing
`current` to prevent registering the observer to signals read in the future.

For this, we remove the `current` argument from the getter, as this is now
globally readable. And we can add the `createEffect` function.

```javascript
function createEffect(fn) {
  current = fn;
  fn();
  current = undefined;
}
```

With this setup, we already have a working signal system. We can register
effects and write out signals to trigger them.

```javascript
const [isSuccess, setSuccess] = setSignal(false);
createEffect(() => console.log(‘We have ‘, isSuccess() ? ‘success!’ : ‘no success yet…’);
// The above line will log “We have no success yet…”

setSuccess(true); // Expected result: We have success!
```

And there we have it! Working signals in just a couple of lines, no magic, no
difficult JavaScript API. All just plain code. You can play around with the
fully working example in
[this StackBlitz project](https://stackblitz.com/edit/node-zuzzhk). It has the
signal setup as described, plus an example of stores. Run `node index.js` to see
the result.

This simple framework is only focused on showcasing signals. For demonstration
purposes, it simply logs to the console. Frameworks like SolidJS have advanced
effects and logic to get HTML rendering to work. If you’re interested in
learning more about rendering, you can read Mark’s blog on
[how to create your own custom renderer in SolidJS](https://www.thisdot.co/blog/how-to-create-your-own-custom-renderer-in-solidjs)

Or put your newly learned skills to use in a new SolidJS project created with
Starter.dev’s
[SolidJS and Tailwind starter](https://starter.dev/kits/solidjs-tailwind/)!
