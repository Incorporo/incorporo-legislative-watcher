# Romanian Legislative Watcher - Visualization Design Specification

A comprehensive guide to all data visualizations for understanding Romanian legislative processes, party performance, risks, and parliament activities.

---

## Table of Contents

1. [Overview](#overview)
2. [Design Principles](#design-principles)
3. [Legislative Process Visualizations](#legislative-process-visualizations)
4. [Party & Representative Performance](#party--representative-performance)
5. [Risk & Vulnerability Monitoring](#risk--vulnerability-monitoring)
6. [Parliament Calendar](#parliament-calendar)
7. [Additional Visualizations](#additional-visualizations)
8. [Technical Implementation](#technical-implementation)
9. [Responsive Design](#responsive-design)
10. [Accessibility](#accessibility)

---

## Overview

### Purpose

Translate complex legislative data into intuitive, actionable insights for:
- **Citizens**: Understand what's happening in Parliament
- **Journalists**: Investigate legislative patterns and anomalies
- **Activists**: Monitor bills affecting their causes
- **Businesses**: Track regulatory changes
- **Researchers**: Analyze legislative trends

### Visualization Philosophy

1. **Clarity over Complexity**: Simple, focused visualizations
2. **Interactivity**: Users can explore data by clicking, filtering, hovering
3. **Context**: Always provide explanations and tooltips
4. **Responsiveness**: Works on desktop, tablet, and mobile
5. **Accessibility**: Color-blind friendly, screen-reader compatible

---

## Design Principles

### Color Palette

#### Primary Colors
- **Blue** (#2563EB): Information, neutral legislative items
- **Green** (#10B981): Passed, approved, positive
- **Red** (#EF4444): Rejected, high risk, urgent
- **Yellow** (#F59E0B): In progress, medium risk, pending
- **Gray** (#6B7280): Inactive, archived, neutral

#### Party Colors (Romanian Parties)
- **PSD**: #FF0000 (Red)
- **PNL**: #FFA500 (Orange)
- **USR**: #00BFFF (Light Blue)
- **AUR**: #FFD700 (Gold)
- **UDMR**: #008000 (Green)
- **Independent**: #808080 (Gray)

#### Risk Level Colors
- **Critical**: #DC2626 (Dark Red)
- **High**: #F59E0B (Orange)
- **Medium**: #FBBF24 (Yellow)
- **Low**: #10B981 (Green)

### Typography

- **Headings**: Inter, Roboto, or system fonts
- **Body**: -apple-system, BlinkMacSystemFont, "Segoe UI"
- **Numbers**: Tabular figures for alignment

### Iconography

Use consistent icon set (Heroicons, Font Awesome, or Tabler Icons):
- 📋 Bills/Documents
- 👥 People/Legislators
- ⚠️ Risks/Alerts
- 📅 Calendar/Timeline
- 📊 Statistics/Charts
- 🏛️ Chamber/Parliament
- ✅ Approved/Passed
- ❌ Rejected/Failed

---

## Legislative Process Visualizations

### 1. **Bill Timeline Visualization**

**Purpose**: Show the journey of a bill through the legislative process

**Design Type**: Horizontal Timeline with Milestones

**Components**:
```
[Registration] ──► [Committee Review] ──► [Debate] ──► [Vote] ──► [Promulgation]
     ✓              ✓ (30 days ago)         ○           ○            ○
```

**Interactive Elements**:
- **Hover**: Show event details (date, description, participants)
- **Click**: Expand to show sub-events (committee meetings, amendments)
- **Color coding**:
  - ✓ Green: Completed
  - ⏱ Yellow: In progress
  - ○ Gray: Pending
  - ⚠️ Red: Delayed (past deadline)

**Data Points**:
- Event date and time
- Event type (registered, committee_review, vote, etc.)
- Duration between stages
- Deadlines and whether they were met
- Committee assignments
- Vote results (if applicable)

**Example UI**:
```
┌────────────────────────────────────────────────────────────────┐
│ Bill B514/2025: Playground in Airports & Train Stations        │
│ Status: Under Committee Review                                 │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Oct 21 ──► Oct 27 ──► Nov 26 ──► ??? ──► ???                 │
│  Registered  Opinions  Deadline   Vote    Promulgation         │
│     ✓          ✓         ⏱        ○        ○                  │
│                                                                 │
│  Timeline: 36 days elapsed | 5 days until deadline             │
├────────────────────────────────────────────────────────────────┤
│  [View Full History] [Download Documents] [Set Alert]          │
└────────────────────────────────────────────────────────────────┘
```

---

### 2. **Legislative Status Flow (Sankey Diagram)**

**Purpose**: Visualize how bills flow through different statuses

**Design Type**: Sankey Diagram or Alluvial Flow

**Components**:
```
Introduced (250) ────┬──► Committee (180) ──┬──► Approved (120)
                     │                       │
                     └──► Rejected (40)      └──► Amended (60)
                     └──► Withdrawn (30)
```

**Filters**:
- Time period (last month, quarter, year)
- Chamber (CDEP, Senate, both)
- Party affiliation
- Bill type

**Interactive**:
- **Hover**: Show exact numbers and percentages
- **Click**: Filter bills by status category

**Insights**:
- Success rate by party
- Bottlenecks in legislative process
- Most common outcomes

---

### 3. **Bill Status Dashboard**

**Purpose**: At-a-glance overview of all bills

**Design Type**: Card Grid + Stats

**Layout**:
```
┌────────────────────────────────────────────────────────────┐
│  Active Bills: 234    │  Passed: 45   │  Rejected: 12     │
│  Urgent: 18           │  Pending: 159 │  In Committee: 87 │
└────────────────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────┬──────────────┐
│ B514/2025    │ L123/2024    │ PL456/2025   │ B789/2025    │
│ Playgrounds  │ Tax Reform   │ Data Privacy │ Education    │
│ 🟡 Committee │ 🟢 Passed    │ 🔴 High Risk │ 🟡 Debate    │
│ 5 days left  │ Enacted      │ Privacy      │ 12 days left │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

**Filters**:
- Status (active, passed, rejected)
- Chamber
- Urgency
- Date range
- Risk level (if AI analyzed)
- Committee

---

### 4. **Committee Assignment Visualization**

**Purpose**: Show which committees are reviewing which bills

**Design Type**: Network Graph or Tree Map

**Network Graph**:
```
        [Economic Committee]
              /    |    \
          Bill1  Bill2  Bill3

        [Justice Committee]
              /    \
          Bill4  Bill5
```

**Metrics**:
- Committee workload (# of bills assigned)
- Average review time
- Bill success rate by committee

---

### 5. **Bill Progress Gauge**

**Purpose**: Individual bill progress indicator

**Design Type**: Circular Progress or Linear Bar

**Example**:
```
┌──────────────────────────┐
│    Bill B514/2025        │
│                          │
│        60%               │
│       ●●●●●○○○           │
│   Committee Review       │
│                          │
│  Next: Plenary Debate    │
│  Expected: Dec 15        │
└──────────────────────────┘
```

---

## Party & Representative Performance

### 6. **Parliament Composition Chart**

**Purpose**: Visualize seat distribution by party

**Design Type**: Semi-Circle Parliament Chart (Arc Diagram)

**Example**:
```
         ┌─────────────────────┐
         │  CDEP Composition   │
         └─────────────────────┘

              PNL (120)
          ●●●●●●●●●●●●●●
        ●●●●●●●●●●●●●●●●●●
      ●●PSD(110)●●●●●●●●USR(55)●●
     ●●●●●●●●●●●●●●●●●●●●●●●●●●●
    ───────────────────────────────
    Total Seats: 330
```

**Interactive**:
- **Hover**: Party name, seat count, percentage
- **Click**: Filter all data by party
- **Animation**: Seat changes over time (slider)

**Implementation**: Use D3.js Parliament Chart library

---

### 7. **Legislator Activity Dashboard**

**Purpose**: Compare MP performance and activity

**Design Type**: Leaderboard + Bar Charts

**Metrics**:
- Bills initiated
- Bills co-sponsored
- Questions asked
- Speeches given
- Committee participation
- Voting attendance rate

**Layout**:
```
┌─────────────────────────────────────────────────────────────┐
│  Top Performers (Last 30 Days)                              │
├─────────────────────────────────────────────────────────────┤
│  1. Ion Popescu (PSD)          25 bills  ████████████ 92%  │
│  2. Maria Ionescu (PNL)        22 bills  ███████████  88%  │
│  3. Andrei Georgescu (USR)     18 bills  █████████    75%  │
└─────────────────────────────────────────────────────────────┘

Filters: [Chamber] [Party] [Time Period] [Activity Type]
```

---

### 8. **Party Performance Comparison**

**Purpose**: Compare legislative output and success rates across parties

**Design Type**: Multi-Bar Chart + Radar Chart

**Metrics**:
- Bills introduced
- Bills passed
- Success rate (% of introduced bills that passed)
- Average time to passage
- Collaboration rate (co-sponsorships across parties)

**Radar Chart Example**:
```
              Activity
                 /\
                /  \
               /    \
              /      \
    Success  ●────────● Collaboration
            /|        |\
           / |        | \
          /  |        |  \
         /   |        |   \
        /    |        |    \
       ●─────●────────●─────●
    Urgency          Effectiveness
```

---

### 9. **Co-Sponsorship Network**

**Purpose**: Visualize collaboration between legislators

**Design Type**: Force-Directed Graph

**Nodes**: Legislators (sized by # of bills)
**Edges**: Co-sponsorships (thickness = # of bills together)
**Colors**: Party affiliation

**Insights**:
- Cross-party collaboration patterns
- Influential legislators (high centrality)
- Isolated vs. collaborative MPs

**Interactive**:
- **Zoom/Pan**: Explore network
- **Hover**: MP details
- **Click**: Highlight all connections
- **Filter**: By party, chamber, time period

---

### 10. **Voting Patterns Matrix**

**Purpose**: Show how often parties/legislators vote together

**Design Type**: Heatmap

**Example**:
```
         PSD  PNL  USR  AUR  UDMR
  PSD    100  65   20   15   40
  PNL    65   100  35   10   55
  USR    20   35   100  5    25
  AUR    15   10   5    100  12
  UDMR   40   55   25   12   100

  Legend: Dark = High Agreement | Light = Low Agreement
```

---

## Risk & Vulnerability Monitoring

### 11. **Risk Dashboard**

**Purpose**: Central hub for identifying problematic bills

**Design Type**: Alert Feed + Heat Map

**Layout**:
```
┌─────────────────────────────────────────────────────────────┐
│  🚨 High-Risk Bills Requiring Attention                     │
├─────────────────────────────────────────────────────────────┤
│  🔴 CRITICAL (3 bills)                                      │
│  ─ B789/2025: GDPR Violation Risk                           │
│  ─ L456/2024: Broad Executive Powers                        │
│  ─ PL123/2025: Rushed Procedure (24h review)                │
│                                                              │
│  🟠 HIGH (8 bills)                                          │
│  ─ B514/2025: Privacy concerns in data collection           │
│  ─ L890/2024: Anti-competitive business regulation          │
│  ...                                                         │
└─────────────────────────────────────────────────────────────┘

[Filter by Risk Category] [Sort by Date] [Subscribe to Alerts]
```

---

### 12. **Risk Category Breakdown**

**Purpose**: Understand types of risks across all legislation

**Design Type**: Donut Chart + Table

**Categories**:
- Privacy & Data Protection
- Business & Economic Impact
- Constitutional Concerns
- Democratic Process (rushed, lack of transparency)
- Individual Rights & Freedoms
- Implementation Challenges

**Example**:
```
        Risk Distribution

      Privacy (35%)  ████████
      Business (25%) ██████
      Constitutional (20%) █████
      Democratic (15%) ████
      Rights (5%) █
```

---

### 13. **Risk Trend Analysis**

**Purpose**: Track risk levels over time

**Design Type**: Line Chart + Area Chart

**Metrics**:
- Total bills flagged per month
- Risk level distribution over time
- Most common risk categories
- Response time (how long until risks are addressed)

**Example**:
```
Risk Flags Over Time

High ┤                          ╭─●
     │                    ╭────╯
Med  │          ╭────●───╯
     │    ╭────╯
Low  ●───╯
     └─────────────────────────────────
      Jan  Feb  Mar  Apr  May  Jun  Jul
```

---

### 14. **Bill Risk Details (Individual Page)**

**Purpose**: Deep dive into specific risks for a bill

**Design Type**: Card Layout with Evidence

**Components**:
```
┌─────────────────────────────────────────────────────────────┐
│  Bill B514/2025: Risk Assessment                            │
├─────────────────────────────────────────────────────────────┤
│  Overall Risk: 🟠 HIGH (Score: 72/100)                      │
│                                                              │
│  🔴 Privacy Risk: Critical                                  │
│  │  Issue: Personal data collection without consent         │
│  │  Evidence: Article 5, paragraph 3 - "all passenger data" │
│  │  Affected: 5M+ annual travelers                          │
│  │  Recommendation: Add GDPR compliance clause              │
│                                                              │
│  🟡 Business Impact: Medium                                 │
│  │  Issue: Cost burden on airport operators                 │
│  │  Evidence: Financial impact not assessed                 │
│  │  Affected: 15 commercial airports                        │
│  │  Recommendation: Conduct cost-benefit analysis           │
└─────────────────────────────────────────────────────────────┘
```

**AI Justification**: Display reasoning from AI analysis
**Human Verification**: Show if risk was verified by experts

---

### 15. **Comparative Risk Analysis**

**Purpose**: Compare similar bills and their risk profiles

**Design Type**: Parallel Coordinates Plot or Spider Chart

**Use Case**: "This bill has similar characteristics to B123/2024 which was flagged for privacy violations"

---

## Parliament Calendar

### 16. **Legislative Calendar View**

**Purpose**: Upcoming votes, deadlines, and events

**Design Type**: Monthly Calendar + Agenda List

**Example**:
```
┌─────────────────────────────────────────────────────────────┐
│                    November 2025                            │
├───┬───┬───┬───┬───┬───┬───┬───┬───┬───┬───┬───┬───┬───────┤
│   │ M │ T │ W │ T │ F │ S │ S │                            │
├───┼───┼───┼───┼───┼───┼───┼───┤                            │
│ 3 │ 4 │ 5 │ 6 │ 7 │ 8 │ 9 │10 │                            │
│   │   │   │🗳️ │   │   │   │   │  Nov 6: Plenary Vote       │
│   │   │   │(3)│   │   │   │   │  - B514/2025 (Playgrounds) │
├───┼───┼───┼───┼───┼───┼───┼───┤  - L789/2024 (Tax Reform)  │
│11 │12 │13 │14 │15 │16 │17 │   │  - PL123/2025 (Education)  │
│   │📋 │   │   │⚠️ │   │   │   │                            │
│   │(2)│   │   │(1)│   │   │   │  Nov 12: Committee Report  │
│   │   │   │   │   │   │   │   │  Nov 15: Opinion Deadline  │
└───┴───┴───┴───┴───┴───┴───┴───┴────────────────────────────┘

🗳️ Plenary Votes  │  📋 Committee Deadlines  │  ⚠️ Urgent Bills
```

**Interactive**:
- **Click Date**: See all events
- **Hover**: Quick preview
- **Filter**: Event type, chamber, priority

---

### 17. **Deadline Tracker**

**Purpose**: Monitor approaching deadlines for bill review

**Design Type**: Sortable Table + Countdown

**Columns**:
- Bill Number
- Title
- Committee
- Deadline
- Days Left
- Status

**Visual Indicators**:
- 🔴 < 3 days
- 🟠 3-7 days
- 🟡 7-14 days
- 🟢 > 14 days

**Example**:
```
┌──────────────────────────────────────────────────────────┐
│  Bill          Deadline      Days Left  Status           │
├──────────────────────────────────────────────────────────┤
│  B514/2025     Nov 26, 2025  🔴 2 days  Under Review     │
│  L789/2024     Dec 5, 2025   🟡 9 days  Awaiting Opinion │
│  PL123/2025    Dec 20, 2025  🟢 24 days Assigned         │
└──────────────────────────────────────────────────────────┘
```

---

### 18. **Plenary Session Schedule**

**Purpose**: Upcoming parliamentary sessions

**Design Type**: Timeline + Agenda

**Information**:
- Session date and time
- Bills to be debated
- Expected votes
- Live stream link (if available)

---

### 19. **Committee Meeting Calendar**

**Purpose**: Track committee schedules

**Design Type**: Weekly/Monthly Grid

**Filters**:
- Committee
- Chamber
- Meeting type (regular, special, hearing)

---

## Additional Visualizations

### 20. **Search & Filter Interface**

**Purpose**: Advanced bill discovery

**Design Type**: Multi-faceted search with live results

**Filters**:
- **Text Search**: Title, description, full text
- **Date Range**: Registration, last updated
- **Status**: Active, passed, rejected, etc.
- **Chamber**: CDEP, Senate, both
- **Urgency**: Urgent only
- **Risk Level**: Critical, high, medium, low
- **Party**: Bill initiator's party
- **Committee**: Assigned committee
- **Tags**: AI-generated topics (privacy, economy, health)

**Live Results**: Update as filters change

---

### 21. **Legislative Topics/Tags Cloud**

**Purpose**: Browse bills by subject matter

**Design Type**: Word Cloud or Tag Grid

**Implementation**: AI-generated tags from bill text

**Example Tags**:
- Privacy (45 bills)
- Healthcare (32 bills)
- Economy (28 bills)
- Education (25 bills)
- Environment (18 bills)

---

### 22. **Bill Comparison Tool**

**Purpose**: Side-by-side comparison of bills

**Design Type**: Two-column comparison table

**Compare**:
- Metadata (sponsor, date, status)
- Timeline progress
- Documents
- AI analysis
- Risks
- Similar provisions

---

### 23. **Activity Feed (Live Updates)**

**Purpose**: Real-time stream of legislative activity

**Design Type**: Twitter-like feed

**Example**:
```
┌─────────────────────────────────────────────────────────┐
│  🔔 Recent Activity                                     │
├─────────────────────────────────────────────────────────┤
│  ✅ 5 minutes ago                                       │
│  Bill L789/2024 passed second reading in Senate         │
│  Vote: 78 for, 22 against, 5 abstentions                │
│                                                          │
│  📋 2 hours ago                                         │
│  Committee report published for B514/2025               │
│  Recommendation: Favorable with amendments              │
│                                                          │
│  🆕 4 hours ago                                         │
│  New bill PL999/2025 registered                         │
│  Title: "Digital Services Tax Reform"                   │
│  Initiator: Govt. of Romania                            │
└─────────────────────────────────────────────────────────┘
```

---

### 24. **Statistics Dashboard**

**Purpose**: High-level system stats

**Design Type**: Metrics cards + charts

**Metrics**:
- Total bills tracked
- Bills updated today
- Average scraping time
- System uptime
- Database size
- Last scrape status

**For Transparency**: Show how the system works

---

### 25. **Export & Share Tools**

**Purpose**: Allow users to export data

**Formats**:
- CSV (bill lists, voting records)
- JSON (API-style data)
- PDF (individual bill reports)
- PNG/SVG (charts and visualizations)
- RSS (subscribe to updates)
- iCal (calendar events)

**Share**:
- Direct link with filters applied
- Embed code for visualizations
- Social media sharing

---

## Technical Implementation

### Frontend Stack

#### Recommended Libraries

**JavaScript Frameworks**:
- **Vue.js 3** or **React** for interactivity
- **Alpine.js** (lightweight alternative for simple interactions)

**Visualization Libraries**:
- **Chart.js**: Simple charts (bar, line, pie, doughnut)
- **D3.js**: Advanced custom visualizations (parliament chart, network graphs, sankey)
- **ApexCharts**: Modern, responsive charts with good defaults
- **vis.js**: Network graphs and timelines

**UI Components**:
- **Tailwind CSS**: Utility-first styling
- **Headless UI** or **Radix UI**: Accessible components
- **Heroicons**: Icon set

**Calendar**:
- **FullCalendar**: Interactive calendar component

**Tables**:
- **TanStack Table** (formerly React Table): Powerful data tables

### Backend API Endpoints

Create RESTful API endpoints for each visualization:

```php
// Bills
GET /api/bills
GET /api/bills/{id}
GET /api/bills/timeline/{id}
GET /api/bills/compare?ids=1,2,3

// Statistics
GET /api/stats/chamber/{chamber}
GET /api/stats/party/{party}
GET /api/stats/legislator/{id}

// Risks
GET /api/risks
GET /api/risks/trending

// Calendar
GET /api/calendar/events
GET /api/calendar/deadlines

// Search
GET /api/search?q=privacy&status=active&risk=high
```

**Response Format**: JSON with metadata

```json
{
  "data": [...],
  "meta": {
    "total": 234,
    "page": 1,
    "per_page": 50
  },
  "links": {
    "next": "...",
    "prev": "..."
  }
}
```

### Caching Strategy

**Redis Cache**:
- Cache expensive queries (30 min - 1 hour)
- Invalidate on bill updates
- Cache visualization data separately from raw data

**Example**:
```php
$chartData = Cache::remember('chart:party-performance', 3600, function() {
    return LegislativeBill::selectRaw('party, COUNT(*) as count')
        ->groupBy('party')
        ->get();
});
```

---

## Responsive Design

### Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

### Mobile Optimizations

1. **Simplify charts**: Show key metrics, hide details
2. **Stack layouts**: Single column instead of grid
3. **Touch-friendly**: Large tap targets (44px minimum)
4. **Swipe gestures**: Navigate timelines, carousels
5. **Progressive disclosure**: Show summary, expand for details

---

## Accessibility

### WCAG 2.1 AA Compliance

1. **Color Contrast**: Minimum 4.5:1 for text
2. **Keyboard Navigation**: All interactions accessible via keyboard
3. **Screen Readers**: ARIA labels, semantic HTML
4. **Focus Indicators**: Clear visual focus states
5. **Alternative Text**: Describe charts and images

### Color-Blind Friendly

Use **patterns** in addition to colors:
- ✓ Checkmarks for passed
- ✗ X for rejected
- ⏱ Clock for pending
- Stripes/hatching for different categories

### Data Tables Alternative

For every chart, provide:
- **Table view toggle**: Raw data in accessible table
- **Export option**: Download data as CSV
- **Summary text**: Screen-reader description

**Example**:
```html
<div role="img" aria-label="Parliament composition: PSD 110 seats, PNL 120 seats, USR 55 seats">
  <canvas id="parliament-chart"></canvas>
</div>

<button onclick="toggleTableView()">View as Table</button>
```

---

## Implementation Priority

### Phase 1: MVP (Weeks 1-4)
1. Bill Timeline Visualization
2. Bill Status Dashboard
3. Search & Filter Interface
4. Legislative Calendar View
5. Activity Feed

### Phase 2: Performance & Risks (Weeks 5-8)
6. Parliament Composition Chart
7. Legislator Activity Dashboard
8. Risk Dashboard
9. Risk Category Breakdown
10. Deadline Tracker

### Phase 3: Advanced Analytics (Weeks 9-12)
11. Legislative Status Flow (Sankey)
12. Co-Sponsorship Network
13. Party Performance Comparison
14. Risk Trend Analysis
15. Bill Comparison Tool

### Phase 4: Enhancements (Ongoing)
16. Voting Patterns Matrix
17. Committee Assignment Visualization
18. Topics/Tags Cloud
19. Export & Share Tools
20. Mobile app optimizations

---

## Example Code Snippets

### 1. Parliament Chart (D3.js)

```javascript
import * as d3 from 'd3';

function drawParliamentChart(data, containerId) {
  const width = 600;
  const height = 400;
  const svg = d3.select(`#${containerId}`)
    .append('svg')
    .attr('width', width)
    .attr('height', height);

  // Parliament chart implementation
  // Based on d3-parliament library
}

const data = [
  { party: 'PSD', seats: 110, color: '#FF0000' },
  { party: 'PNL', seats: 120, color: '#FFA500' },
  { party: 'USR', seats: 55, color: '#00BFFF' },
  { party: 'AUR', seats: 30, color: '#FFD700' },
  { party: 'UDMR', seats: 15, color: '#008000' },
];

drawParliamentChart(data, 'parliament-container');
```

### 2. Bill Timeline (Tailwind + Alpine.js)

```html
<div class="timeline" x-data="{ selected: null }">
  <div class="flex items-center space-x-4">
    <template x-for="(event, index) in events" :key="index">
      <div @click="selected = index" class="timeline-item">
        <div :class="event.completed ? 'bg-green-500' : 'bg-gray-300'"
             class="w-8 h-8 rounded-full flex items-center justify-center">
          <span x-show="event.completed">✓</span>
        </div>
        <p class="text-sm mt-2" x-text="event.name"></p>
        <p class="text-xs text-gray-500" x-text="event.date"></p>
      </div>
    </template>
  </div>

  <div x-show="selected !== null" class="mt-4 p-4 bg-gray-100 rounded">
    <h3 x-text="events[selected]?.name"></h3>
    <p x-text="events[selected]?.description"></p>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('timeline', () => ({
    events: [
      { name: 'Registered', date: '2025-10-21', completed: true, description: '...' },
      { name: 'Committee', date: '2025-10-27', completed: true, description: '...' },
      { name: 'Debate', date: null, completed: false, description: '...' },
    ],
    selected: null
  }));
});
</script>
```

### 3. Laravel API Endpoint

```php
// routes/api.php
Route::get('/bills/timeline/{id}', [BillController::class, 'timeline']);

// app/Http/Controllers/BillController.php
public function timeline($id)
{
    $bill = LegislativeBill::with('timeline')->findOrFail($id);

    return response()->json([
        'bill' => [
            'id' => $bill->id,
            'title' => $bill->title,
            'status' => $bill->status,
        ],
        'timeline' => $bill->timeline->map(function ($event) {
            return [
                'date' => $event->event_date->format('Y-m-d'),
                'type' => $event->event_type,
                'description' => $event->description,
                'completed' => $event->event_date <= now(),
            ];
        }),
    ]);
}
```

---

## Conclusion

This comprehensive visualization system transforms raw legislative data into actionable insights. By combining:
- **Clear, intuitive designs**
- **Interactive exploration**
- **Real-time updates**
- **Risk monitoring**
- **Performance tracking**

...we empower citizens, journalists, and organizations to understand and engage with Romanian legislative processes like never before.

---

**Document Version**: 1.0
**Last Updated**: 2025-11-17
**Status**: Design Specification Complete
