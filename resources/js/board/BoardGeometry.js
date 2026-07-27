/**
 * Geometri jalur SATU konektor papan (ular/tangga): titik awal/akhir (satuan
 * kotak — sama dengan CoordinateHelper), jarak, arah, sudut, dan "lane"
 * (offset tegak lurus jalur, deterministik dari posisi asal+akhir — BUKAN
 * random tiap render) supaya konektor yang jalurnya berdekatan/silang tidak
 * persis bertumpuk pada garis yang sama.
 *
 * Kelas ini murni matematika — tidak tahu apa-apa soal SVG, sprite, atau
 * DOM. Dipakai oleh SnakeRenderer & LadderRenderer untuk kedua jenis
 * konektor dengan cara yang identik.
 */
const LANES = [0]; // Jalur selalu di tengah, tidak ada offset ke samping
export class BoardGeometry {
    /** @param {import('./CoordinateHelper').CoordinateHelper} coordHelper */
    constructor(coordHelper) {
        this.coord = coordHelper;
    }

    connector(fromPosisi, toPosisi) {
        const from = this.coord.cellCenterUnit(fromPosisi);
        const to = this.coord.cellCenterUnit(toPosisi);
        const dx = to.x - from.x;
        const dy = to.y - from.y;
        const distance = Math.max(Math.hypot(dx, dy), 0.3);
        const ux = dx / distance;
        const uy = dy / distance;
        const px = -uy;
        const py = ux;
        const angleDeg = (Math.atan2(dy, dx) * 180) / Math.PI;
        const lane = LANES[Math.abs(fromPosisi * 31 + toPosisi * 17) % LANES.length];

        return {
            from,
            to,
            dx,
            dy,
            distance,
            ux,
            uy,
            px,
            py,
            angleDeg,
            lane,
            laneOffset: { x: px * lane, y: py * lane },
        };
    }
}
