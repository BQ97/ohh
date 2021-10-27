export default {
    Bitwise: {
        insert: (rowInt, ...items) => items.reduce((carry, item) => carry | item, rowInt),

        delete: (rowInt, ...items) => items.reduce((carry, item) => carry & (~item), rowInt),

        check: (rowInt, item) => (rowInt & item) === item,

        decbin: (rowInt) => rowInt.toString(2),
    }
}