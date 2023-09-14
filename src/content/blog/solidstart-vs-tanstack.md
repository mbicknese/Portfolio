---
title: 'Challenges of SSR with SolidStart and TanStack Query v4'
pubDate: 'Jul 07 2023'
original: 'https://www.thisdot.co/blog/challenges-of-ssr-with-solidstart-and-tanstack-query-v4/'
heroImage: '/solidstart-vs-tanstack.png'
---

Coming from
[developing in React](https://starter.dev/kits/cra-rxjs-styled-components/), a
lot of us are big fans of [TanStack Query](https://tanstack.com/query/latest).
It adds that layer for async data fetching to React we needed. So when shifting
to a new framework, [Solid](https://www.solidjs.com/), which has a familiar
signature as React, we wanted to bring our beloved tools with us.

During the development of our showcase, we came to realize that the combination
of TanStack Query (v4, v5 seems to include positive changes) and
[SolidStart](https://start.solidjs.com) was not meant to be.

## Understanding the differences

### Different interface

Right out of the box, the experience between Solid and React differs. There’s
the first very obvious issue that the documentation for Solid consists of a
single page, whereas React gets a full book on documentation.

But more important is the way one uses TanStack Query. React directly takes the
tuple containing the query name and variables. Where Solid, due to the way
reactivity works, needs a function returning the tuple. This way, Solid can bind
an effect to the query to ensure it triggers when the dependencies change. It’s
not a big difference, but it indicates that TanStack Query React and TanStack
Query Solid are not the same.

```tsx
// ❌ react version
useQuery([\"todos\", todo], fetchTodos)

// ✅ solid version
createQuery(() => [\"todos\", todo()], fetchTodos)
```

— [TanStack Query Docs](https://tanstack.com/query/latest/docs/solid/overview)

### Stores

What is not so apparent from the documentation are the changes under the hood.
React triggers rerenders when state changes are pushed. These rerenders will, in
turn, compare the new variables against dependencies to determine what to run.
This does not require special treatment of the state. Whatever data is passed to
React will be used directly as is.

Solid, on the other hand, requires
[Signals](https://www.thisdot.co/blog/deep-dive-into-how-signals-work-in-solidjs/)
to function. To save you the hassle, TanStack will create stores from the
returned data for you. With the dependency tuple as a function and the return
value as store, TanStack Query closes the reactivity loop. Whenever a signal
changes, the query will be triggered and load new data. The new data gets
written to the store, signalling all observers.

## Why it doesn’t work

Solid comes prepacked with Resources. These basically fill the same
functionality as TanStack Query offers. Although TanStack does offer more
features for the React version. Resources are Signal wrappers around an async
process. Typically they’re used for fetching data from a remote source.

Although both Resources and TanStack Query do the same thing, the different
signatures makes it so they’re not interchangeable. Resources have `loading`
where TanStack uses `isLoading`.

### SolidStart

SolidStart is an opinionated meta-framework build on top of SolidJS and Solid
router. One of the features it brings to the table is Server-side rendering
(SSR). This sends a fully rendered page to the client, as opposed to just
sending the skeleton HTML and having the client build the page after the initial
page load. With SSR, the server also send additional information to the client
for SolidJS to hydrate and pick up where the server left off. This prevents the
client from re-rendering all the work the server had already done.

In order for SSR to work, one needs to create pages. SolidStart offers a feature
that allows developers to inject data into their pages. By doing so, one can set
up a generic GUI for loading data when changing between pages. A very minimal
example of this looks like:

```JavaScript
export function routeData() {
  const [count] = createSignal(4);
  return count;
}

export defaulft function Page() {
  const count = useRouteData();
  return <p>The current count is {count()}</p>;
}
```

When combining this setup with routing and `createResource`, there’s some
caveats that need to be taken into consideration. These are described in the
official
[SolidStart docs](https://start.solidjs.com/core-concepts/data-loading). In
order to keep the routes maintainable, SolidStart offers `createRouteData` that
simplifies the setup and to mitigate potential issues caused by misusing the
system.

### createRouteData, resources and TanStack Query

It is with `createRouteData` that we run into issues with combining SolidStart
and TanStack Query. In order to use SSR, SolidStart needs the developers to use
`createRouteData`. Which in turn expects to create a resource for the async
operation that is required to load the page’s data.

By relying on a resource being returned, SolidStart can take control of the
flow. It knows when it’s rendering on the server, how to pass both the HTML and
the data to server, and finally how to pick up on the client.

As stated before, TanStack Query relies on stores, not on resources. Therefore
we cannot swap out `createRouteData` and `createQuery` even though they both
fill the same purpose. Our initial attempt was to wrap the returned data from
`createQuery` to resemble the shape of a resource. But that started to throw
errors as soon as we tried to load a page.

Under the hood, both SolidStart and TanStack Query are doing their best to hold
control over the data flow. Systems like caching, hydration strategies and
refetching logic are running while it seems like we’re just fetching data and
passing it to the render engine. These systems conflict (they both are trying to
do the same thing and get stuck in a tug-o-war for the data). This results in a
situation where we can either satisfy TanStack Query or SolidStar.

We can probably make it work by creating an advanced adapter that awaits and
pulls the data from a query. Use that data to create our own resource and feed
that to `createRouteData` to have SolidStart do its thing. Our conclusion is
that there’s too much effort needed to create and maintain such an adapter
especially when taking into consideration that we can simply move away from
TanStack Query (for now) and use resources as SolidStart intents.
