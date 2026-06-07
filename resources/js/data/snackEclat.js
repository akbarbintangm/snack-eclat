export const transactions = [
    { id: 'T1', items: ['Pop U Corn OG', 'Pop U Corn Cheese'] },
    { id: 'T2', items: ['Pop U Corn Cheese', 'Pop U Corn Choco'] },
    { id: 'T3', items: ['Pop U Corn OG', 'Pia Kuno 10pcs'] },
    { id: 'T4', items: ['Pop U Corn Cheese', 'Pia Kuno 10pcs'] },
    { id: 'T5', items: ['Pop U Corn OG', 'Pop U Corn Choco'] },
    { id: 'T6', items: ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pop U Corn Choco'] },
    { id: 'T7', items: ['Pop U Corn Cheese', 'Pia Kuno 10pcs'] },
    { id: 'T8', items: ['Pop U Corn OG', 'Pia Kuno 10pcs'] },
    { id: 'T9', items: ['Pop U Corn OG', 'Pop U Corn Cheese'] },
    { id: 'T10', items: ['Pop U Corn Choco', 'Pia Kuno 10pcs'] },
    { id: 'T11', items: ['Pop U Corn OG', 'Pop U Corn Cheese'] },
    { id: 'T12', items: ['Pop U Corn OG', 'Pop U Corn Choco'] },
    { id: 'T13', items: ['Pop U Corn Cheese', 'Pop U Corn Choco'] },
    { id: 'T14', items: ['Pop U Corn OG', 'Pia Kuno 10pcs'] },
    { id: 'T15', items: ['Pop U Corn Cheese', 'Pia Kuno 10pcs'] },
    { id: 'T16', items: ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pia Kuno 10pcs'] },
    { id: 'T17', items: ['Pop U Corn Choco', 'Pia Kuno 10pcs'] },
    { id: 'T18', items: ['Pop U Corn OG', 'Pop U Corn Cheese'] },
    { id: 'T19', items: ['Pop U Corn OG', 'Pop U Corn Choco'] },
    { id: 'T20', items: ['Pop U Corn Cheese', 'Pia Kuno 10pcs'] },
    { id: 'T21', items: ['Pop U Corn OG', 'Pop U Corn Cheese'] },
    { id: 'T22', items: ['Pop U Corn Choco', 'Pia Kuno 10pcs'] },
    { id: 'T23', items: ['Pop U Corn OG', 'Pia Kuno 10pcs'] },
    { id: 'T24', items: ['Pop U Corn Cheese', 'Pop U Corn Choco'] },
    { id: 'T25', items: ['Pop U Corn OG', 'Pop U Corn Cheese', 'Pop U Corn Choco'] },
];

export const reportThresholds = {
    minSupport: 30,
    minConfidence: 50,
};

export function formatPercent(value) {
    return `${new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
        minimumFractionDigits: Number.isInteger(value) ? 0 : 2,
    }).format(value)}%`;
}

export function buildEclatSummary(minSupport = reportThresholds.minSupport, minConfidence = reportThresholds.minConfidence) {
    const totalTransactions = transactions.length;
    const tidMap = new Map();

    transactions.forEach((transaction) => {
        transaction.items.forEach((item) => {
            if (!tidMap.has(item)) {
                tidMap.set(item, []);
            }

            tidMap.get(item).push(transaction.id);
        });
    });

    const products = Array.from(tidMap.keys()).sort();

    const oneItemsets = products
        .map((item) => {
            const tids = tidMap.get(item);

            return createItemset([item], tids, totalTransactions);
        })
        .sort(sortBySupport);

    const pairItemsets = [];

    products.forEach((leftItem, leftIndex) => {
        products.slice(leftIndex + 1).forEach((rightItem) => {
            const tids = intersect(tidMap.get(leftItem), tidMap.get(rightItem));
            pairItemsets.push(createItemset([leftItem, rightItem], tids, totalTransactions));
        });
    });

    pairItemsets.sort(sortBySupport);

    const frequentItemsets = [...oneItemsets, ...pairItemsets]
        .filter((itemset) => itemset.support >= minSupport)
        .sort(sortBySupport);

    const rules = pairItemsets
        .flatMap((itemset) => {
            const [leftItem, rightItem] = itemset.items;

            return [
                createRule(leftItem, rightItem, itemset, tidMap, totalTransactions),
                createRule(rightItem, leftItem, itemset, tidMap, totalTransactions),
            ];
        })
        .filter((rule) => rule.support >= minSupport && rule.confidence >= minConfidence)
        .sort((left, right) => right.confidence - left.confidence || right.support - left.support);

    return {
        totalTransactions,
        products,
        tidList: oneItemsets,
        oneItemsets,
        pairItemsets,
        frequentItemsets,
        rules,
        topRule: rules[0] ?? null,
    };
}

function createItemset(items, tids, totalTransactions) {
    return {
        key: items.join(' + '),
        label: items.join(', '),
        items,
        tids,
        count: tids.length,
        support: toPercent(tids.length, totalTransactions),
    };
}

function createRule(antecedent, consequent, itemset, tidMap, totalTransactions) {
    const antecedentCount = tidMap.get(antecedent).length;

    return {
        key: `${antecedent}->${consequent}`,
        antecedent,
        consequent,
        label: `${antecedent} -> ${consequent}`,
        support: toPercent(itemset.count, totalTransactions),
        confidence: toPercent(itemset.count, antecedentCount),
    };
}

function intersect(leftTids, rightTids) {
    const rightSet = new Set(rightTids);

    return leftTids.filter((tid) => rightSet.has(tid));
}

function toPercent(value, total) {
    return Number(((value / total) * 100).toFixed(2));
}

function sortBySupport(left, right) {
    return right.support - left.support || left.label.localeCompare(right.label);
}
