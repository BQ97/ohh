export default {
    Bitwise: {
        insert: (rowInt, ...items) => items.reduce((carry, item) => carry | item, rowInt),

        delete: (rowInt, ...items) => items.reduce((carry, item) => carry & (~item), rowInt),

        check: (rowInt, item) => (rowInt & item) === item,

        decbin: (rowInt) => {
            let row = rowInt.toString(2);
            return {
                row,
                arr: row.split('')
            };
        }
    }
}