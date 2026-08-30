const stats = [
  ['Courses', '0'],
  ['Teachers', '0'],
  ['Sections', '0'],
  ['Rooms', '0'],
];

export default function Home() {
  return (
    <main style={{ minHeight: '100vh', padding: '48px', fontFamily: 'system-ui, sans-serif' }}>
      <section style={{ maxWidth: 1100, margin: '0 auto' }}>
        <p style={{ fontSize: 14, fontWeight: 700, letterSpacing: 1 }}>SMART TIMETABLE</p>
        <h1 style={{ fontSize: 48, lineHeight: 1.1, margin: '18px 0 12px' }}>
          Plan. Generate. Optimize. Publish.
        </h1>
        <p style={{ maxWidth: 680, fontSize: 18, lineHeight: 1.6, opacity: 0.7 }}>
          A modern academic scheduling platform for courses, teachers, sections, rooms,
          conflict detection and intelligent timetable generation.
        </p>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginTop: 48 }}>
          {stats.map(([label, value]) => (
            <div key={label} style={{ border: '1px solid #ddd', borderRadius: 12, padding: 20 }}>
              <div style={{ fontSize: 13, opacity: 0.65 }}>{label}</div>
              <div style={{ fontSize: 30, fontWeight: 700, marginTop: 8 }}>{value}</div>
            </div>
          ))}
        </div>
      </section>
    </main>
  );
}
